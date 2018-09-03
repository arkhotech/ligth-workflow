<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Process;
use App\Domain;
use App\Role;
use App\ProcessRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use App\Variable;
use App\ProcessInstance;
use App\Jobs\AsyncStart;
use App\Events\Events;
use App\Events\ProcessEvent;
use App\Exceptions\NotUserInRoleException;

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
    
    private function assignRoles($newRoles,$process){
        
        if($newRoles == null){
            //rol por defecto
            $newRoles = array("admin");
        }
        
        foreach($newRoles as $rol_name){
                //El rol debe existir
            $rol = Role::where('name',$rol_name)->first();
            if($rol==null){
                return response()->nofound("El rol $rol_name no existe");
            }

            if( $process->roles()->where("roles.name",$rol_name)->first() == null){
                Log::debug("Creando la relacion process role");
                $process_role = new ProcessRole();
                $process_role->process_id = $process->id;
                $process_role->role_id = $rol->id;
                $process_role->save();
            }else{
                //El rol esta asignado
                Log::debug("Rol existe");
            }
        }
    }
    
    private function removeRoles($process, $newRoles){
        //Intercambia los valores (roles) a claves para busqueda
        $asignedRoles = $process->roles()->get();
        $roles = array_flip($newRoles); 
        foreach($asignedRoles as $role){
            Log::debug("Buscando: ".$role->name);
            
            if(!key_exists($role->name, $roles)){
                ProcessRole::where('role_id',$role->id)
                        ->where('process_id',$process->id)
                        ->delete();
            }
        }
    }
    
    public function updateProcess(Request $request, $id_process){
        $process = Process::find($id_process);
        
        if( $process != null ){
            foreach($process->fields() as $field){
                if($request->input($field) == null){
                    continue;
                }
                $process->{$field} = $request->input($field);
            }
            //Revisar roles asignados
            $newRoles = $request->input("assign_roles.*");
            $this->assignRoles($newRoles,$process);
            //revisar roles que no están asignados
            $this->removeRoles($process,$newRoles);
            
            $process->save();
            $process->roles = $process->roles()->select("name")->get();
            return response()->json($process,200);
        }
        return response()->nofound("El proceso $id_process no existe");
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
            return response()->nofound("dominio no existe: $id_domain");
        }
       
        $process = Process::where("domain_id",$id_domain)->get();
        
        return response()->json($process,200);
    }
    /**
     * Crea una nueva definicion de proceso
     * 
     * @param Request $request
     * @param type $domain_id Idetificador del dominio sobre el cual se creará el proceso
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
        //Asignar los roles necesarios
        $this->assignRoles($request->input('roles.*'), $process);
        
        $variables = $request->input('variables.*');
        foreach($variables as $item){
            $new_var = new Variable();
            $new_var->name = $item['name'];
            $new_var->process_id = $process->id;
            $new_var->type = $item['type'];
            //$new_var->validator = $item['validator'];
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
    //TODO quizá moverla a control...
   public function startProcess(ProcessRequest $request, $id_process){
        //Log::debug("User: ".Auth::user());
        $request->validate(['parameters' => 'array']);
        //Solo para validar el proceso
        $process = Process::find($id_process);
        
        if($process != null){
            DB::beginTransaction();
            //check si el usuario es aninimo o no (usuario anonimo 0) 
            $process_instance = $process->newProcessInstance(Auth::user());
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
                
                //validar que entrada sea igual 
                //ELIMINAR Todos los procesos comienzan asincronamente. validar si es un proceso sincrono 
                if($process_instance->asynch){
                   
                    $id_job = $this->dispatch(new AsyncStart($process_instance->id));
                    Log::info('Trabajo despachado, id: '.$id_job);
                    DB::commit();
                    return response()->json(
                            array("id_process" => $process_instance->id,
                                "id_job" => $id_job),202);
                }else{
//#######  Run process
                    //Iniciar el proceso
                    event(new ProcessEvent($process_instance,Events::IDLE));
                    //$result = $this->runProcess($process_instance);
                    DB::commit();
                    return response()->json(
                            array("id_process" => $process_instance->id, 
                                "output" => $result),201);
                }
            }catch(NotUserInRoleException $e){
                DB::rollback();
                return response()->json(["message" => $e->getMessage()],403);
            }catch(Throwable $e){
               
               return response(null,500);
            }

        }
        return response(null,404);
    }
    
    private function checkVariables($keys,$params,$id_instance){
        
        $error = array();
        foreach($keys as $key ){
            Log::debug("Check var: ".$key);
            $value = Input::get('parameters.'.$key);
            Log::debug("      val: ".$value);
            if($value===null){    
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
    
    private function runProcess(ProcessInstance $process_instance){
        //TODO ver los requisitos y post procesos
        try{
            
 // ##### Ejeucion de proceso
            //Solo trae la primera instancia
            $activity_instance = $process_instance->run();
            //Este loop es necesario si es que son solo actividades
            while($activity_instance != null 
                    && $activity_instance->activity_state != ActivityEvents::PENDDING  ){  
                // si esta pendiente se rompe el loop. 
                // Pendiente es por qu enecesita intervención humana o termino
                Log::info("Procesando instancia de actividad: ".$activity_instance->id);
                //actualizando el cursor para saber en que actividad esta el proceso
                $process_instance->activityCursor = $activity_instance->id;
                $process_instance->save(); 
// #######  Ejecucion de actividad
                //ejecutar efectivamente la instancia
                $next_activity = $activity_instance->executeActivity();
                //Aca podría retornar una o varias instancias si es que hay caminos paralelos
                
                $activity_instance = $next_activity;
            }
            //
            if($activity_instance != null ){
               Log::debug("### saliendo del loop, status = ".$activity_instance->activity_state);
                $process_instance->process_state =$activity_instance->activity_state;
            }else{
                Log::debug("### Finalizando, Finished");
                $retval = array();
                $this->collectResult($process_instance,$retval);
                $process_instance->process_state = ActivityEvents::FINISHED;
                return $retval;
            }
            $process_instance->saveOrFail();
        }catch(Exception $e){
            $process_instance->process_state = ActivityEvents::ERROR;
            $process_instance->save();
            throw $e;
            
        }
    }
    
    private function collectResult($process_instance,&$retval){
        $activity_instances = $process_instance->activitiesInstances()->with('actionsInstances')->get();
        foreach($activity_instances as $activity_instance){
            $this->collectActivityResult($activity_instance->actionsInstances,$retval);//array($action_result->name => $action_result->output);
        }
    }
    
    private function collectActivityResult($res,&$retval){
        foreach($res as $action_result){
            $retval[] = array($action_result->name => json_decode($action_result->output));
        }
    }
  
}




class ProcessRequest extends Request{
    
}