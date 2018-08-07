<?php

namespace App\Http\Controllers;

use App\ProcessInstance;
use App\Process;
use Illuminate\Http\Request;
use App\ProcessVariable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class ProcessInstanceController extends Controller
{
    
    /**
     * 
     * @param Request $request
     * @param type $id_process
     * @return type
     */
   public function createInstance(Request $request, $id_process){
        Log::debug("User: ".Auth::user());
        $request->validate(['parameters' => 'array']);
        
        $process = Process::find($id_process);
        
        if($process != null){
            //check si el usuario es aninimo o no (usuario anonimo 0) 
            $process_instance = Process::newProcessInstance($process);
            $id_instance = $process_instance->id;
            $declared_vars = $process->declaredVariables()->get();
            
            $keys = array();
            foreach($declared_vars as $var){
                $keys[$var->name] = $var;
            }
            try{
                DB::beginTransaction();
                $error = [];
                foreach(Input::get('parameters') as   $item){
                    if( key_exists(key($item),$keys)){
                        
                        $param = new ProcessVariable();
                        $param->name = key($item);
                        $param->value = json_encode($item[key($item)]);
                        $param->id_process = $id_instance;
                        $param->save();
                    }else{
                        $error[] =  key($item);
                    }
                }
                
                if(count($error) != 0 ){
                     DB::rollback();
                    return response()
                                ->json(
                        array("variables.no.declaradas" => $error),404);
                }
                $process_instance->start();
                DB::commit();
                return response(null,201);
            }catch(Throwable $e){
               DB::rollback();
               return response(null,500);
            }
            
            return response()->json(array('instance_id'=> $id_instance),201);
        }
        return response(null,404);
    }
    
    public function instances($id_proceso){
        $process = Process::where("id",$id_proceso)->first();
        if($process!= null){
            return response()->json($process->instances()->get());
        }
        return response(null,404);
        
    }
   
}
