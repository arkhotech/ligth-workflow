<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Process;
use App\Domain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\ProcessVariable;
use App\ProcessInstance;
use App\Jobs\AsyncStart;
use App\Events\ActivityEvents;

class ProcessController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request){
        //$this->middleware("auth:api");
    }
    
 
    /**
     * Losta procesos creados
     * 
     * @param type $id_domain
     * @return type
     */
    public function listProcess($id_domain){
        //validar dominio
        if(Domain::find($id_domain)==null){
            return response()->json(array("status" => "error","message" => "dominio no existe"),404);
        }
       
        $process = Process::where("domain_id",$id_domain)->get();
        
        return response()->json($process,200);
    }
    /**
     * Crea un nuevo proceso
     * @param Request $request
     * @param type $domain_id
     * @return type
     */
    public function newProcess(Request $request,$domain_id){
        $request->validate([
            'name' => 'required|string',
            'role_owner_id' => 'required|integer',
            'variables' => 'array'
        ]);
        DB::beginTransaction();
               
        //check Si existe:
        if(Process::where("name","=",$request->input('name'))
                ->where('role_owner_id',"=",$request->input('role_owner_id'))->first()!=null){
            return response()->json(['status' => 'registro existe'],409);
        }
        $process = new Process();
        
        foreach($process->fields() as $field){
            if($field == 'domain_id'){
                $process->domain_id = $domain_id; 
                continue;
            }
           $process->{$field} = $request->input($field);
        } 
        
        $process->save();
        $variables = $request->input('variables.*');
        foreach($variables as $item){
            $new_var = new ProcessVariable();
            $new_var->name = $item['name'];
            $new_var->process_id = $process->id;
            $new_var->type = $item['type'];
            $new_var->validator = $item['validator'];
            $new_var->save();
        }
        
        DB::commit();
        return response()->json(['id_process' => $process->id ],201);
        
    }
    
    public function deleteProcess($id){
        $process = Process::find($id);
        if($process){
            $process->delete();
        }else{
            return response(null,404);
        }
        return response(null,200);
    }
    
    public function listTrashedProcess($id_domain){
        $trashed =Process::onlyTrashed()->where('domain_id',$id_domain)->get();
        return response()->json($trashed);
    }
    
    public function restoreProcess($id_process){
        $process = Process::onlyTrashed()->find($id_process);
        if($process != null){
            $process->restore();
            return response()->json($process,201);
        }else{
            return response(null,404);
        }
    }
    
        /**
     * Crea una instancia e inicia un proceso
     * 
     * @param Request $request
     * @param type $id_process
     * @return type
     */
    
   public function createInstance(ProcessRequest $request, $id_process){
        //Log::debug("User: ".Auth::user());
        $request->validate(['parameters' => 'array']);
        //Solo para validar el proceso
        $process = Process::find($id_process);
        
        if($process != null){
            DB::beginTransaction();
            //check si el usuario es aninimo o no (usuario anonimo 0) 
            $process_instance = $process->createInstance();
            $id_instance = $process_instance->id;
            Log::debug("Id nueva instancia: ".$id_instance);
            $declared_vars = $process
                    ->declaredVariables()
                    ->select("id","name","type")
                    ->get();
            
            $keys = $params = $error = array();
            $params = array();
            foreach($declared_vars as $var){
                $keys[] = $var->name;
                $params[$var->name] = $var;
            }
            
            try{
                //Check cantidad de variabels de entrada. 
                Log::debug("Check de variables");
                $result = $this->checkVariables($keys, $params,$process_instance);
                
                if(count($result) != 0 ){
                    DB::rollback();
                    return response()->json($result,404);
                }
                DB::commit();
                //validar que entrada sea igual 
                //TODO validar si es un proceso sincrono
                if($process_instance->asynch){
                   
                    $id_job = $this->dispatch(new AsyncStart($process_instance->id));
                    Log::info('Trabajo despachado, id: '.$id_job);
                    return response()->json(
                            array("id_process" => $process_instance->id,
                                "id_job" => $id_job),202);
                }else{
                    //var_dump($process_instance);die;
                    $result = $this->runProcess($process_instance);
                    return response()->json(
                            array("id_process" => $process_instance->id, 
                                "output" => $result),201);
                }
            }catch(Throwable $e){
               DB::rollback();
               return response(null,500);
            }

        }
        return response(null,404);
    }
    
    private function checkVariables($keys,$params,$id_instance){
        
        $error = array();
        foreach($keys as $key ){
            
            $value = Input::get('parameters.'.$key);
            Log::debug("val: ".$value);
            if($value==null){    
                $error[] = array("variable.no.declarada" => $key); continue;
            }
            $var = $params[$key]->createInstance($id_instance); 
            
            $var->value = $value;
            $var->save();
            if( ! $var->validate() ) {
                Log::debug("Error de validacion variable: ".$key);
                $error[] = array("error.validacion" => $key, 
                    "tipo" => $var->type,
                    "valor" => $var->value);
            }else{
                $var->save();
            }
        }
        
        return $error;
    }
    
    private function runProcess(ProcessInstance $process){
        //TODO ver los requisitos y post procesos
        try{
            //Solo trae la primera instancia
            $activity_instance = $process->onActivity();
            
            while($activity_instance != null 
                    && $activity_instance->activity_state != ActivityEvents::PENDDING  ){  
                // si esta pendiente se rompe el loop
                Log::info("Procesando instancia de actividad: ".$activity_instance->id);
                //actualizando el cursor para saber en que actividad esta el proceso
                $process->activityCursor = $activity_instance->id;
                $process->save(); 
                //ejecutar efectivamente la instancia
                $next_activity = $activity_instance->executeActivity();
                
                $activity_instance = $next_activity;
            }
            Log::debug("### saliendo del loop, status = ".$activity_instance->activity_state);
            $process->process_state = ($activity_instance != null ) ? 
                $activity_instance->activity_state : 
                ActivityEvents::FINISHED;
            $process->saveOrFail();
        }catch(Exception $e){
            $process->process_state = ActivityEvents::ERROR;
            $process->save();
        }
    }
  
}


class ProcessRequest extends Request{
    
}