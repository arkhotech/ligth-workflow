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
    
 
    
    public function listProcess($id_domain){
        //validar dominio
        if(Domain::find($id_domain)==null){
            return response()->json(array("status" => "error","message" => "dominio no existe"),404);
        }
       
        $process = Process::where("domain_id",$id_domain)->get();
        
        return response()->json($process,200);
    }
    
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
        
        foreach(Process::fields as $field){
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
     * Crea e inicia un proceso
     * 
     * @param Request $request
     * @param type $id_process
     * @return type
     */
    
   public function createInstance(Request $request, $id_process){
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
                    ->select("id","name","value","type")
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
                $process_instance->start();
                
                return response(null,201);
            }catch(Throwable $e){
               DB::rollback();
               return response(null,500);
            }
            
            return response()->json(array('instance_id'=> $id_instance),201);
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
                $error[] = array("error.validacion" => $key, "tipo" => $var->type, "valor" => $var->value);
            }else{
                $var->save();
            }
        }
        
        return $error;
    }
  
}
