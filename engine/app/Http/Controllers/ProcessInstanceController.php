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
     * Crea e inicia un proceso
     * 
     * @param Request $request
     * @param type $id_process
     * @return type
     */
    
   public function createInstance(Request $request, $id_process){
        Log::debug("User: ".Auth::user());
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
                $result = $this->checkVariables($keys, $params);
                
                if(count($result) != 0 ){
                    DB::rollback();
                    return response()->json($result,404);
                }
                
                //validar que entrada sea igual 
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
    
    private function checkVariables($keys,$params){
        
        $error = array();
        foreach($keys as $key ){
            
            $value = Input::get('parameters.'.$key);
            Log::debug("val: ".$value);
            if($value==null){
                $error[] = array("variable.no.declarada" => $key); continue;
            }
            $var = $params[$key]->createInstance(); 
            $var->value = $value;
            
            if( ! $var->validate() ) {
                Log::debug("Error de validacion variable: ".$key);
                $error[] = array("error.validacion" => $key, "tipo" => $var->type, "valor" => $var->value);
            }else{
                $var->save();
            }
        }
        
        return $error;
    }
    
    public function instances($id_proceso){
        $process = Process::where("id",$id_proceso)->first();
        if($process!= null){
            return response()->json($process->instances()->get());
        }
        return response(null,404);
        
    }
   
}
