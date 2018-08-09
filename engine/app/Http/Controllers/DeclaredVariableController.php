<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Process;
use App\ProcessVariable;
use App\ActivityVariable;
use Illuminate\Support\Facades\DB;

class DeclaredVariableController extends Controller{
   
    public function listProcessVariables($id_process){
        $process = Process::find($id_process);
        if( $process != null ) {
            $vars = $process->declaredVariables()
                    ->select('id','id_process','name','value','type')
                    ->get();
            return response()->json($vars,200);
        }
        return response(null,404);
    }
    
    public function listActivityVariables($id_activity){
        $activity = Activity::find($id_activity);
        if( $activity != null ) {
            $vars = $activity->declaredVariables()
                    ->select('id','id_process','name','value','type')
                    ->get();
            return response()->json($vars,200);
        }
        return response(null,404);
    }
    
    public function addGlobalVariables(Request $request,$id_process){
        $request->validate(['variables' => 'required|array']);
        if(Process::find($id_process)!=null){
            try{
                DB::beginTransaction();
                foreach( $request->input('variables.*') as $var ){
                    Log::debug($var['name']);
                    $pvar = new ProcessVariable();
                    $pvar->name = $var['name'];
                    $pvar->value = $var['value'];
                    $pvar->id_process = $id_process;
                    $pvar->save();
                }
                DB::commit();
            }catch(Exception $e){
                Log::error($e->getException());
                DB::rollback();
            }
            return response(null,201);
        }
        return response(null,404);
    }
    
    public function removeGlobalVariables(Request $request, $id_process){
        $request->validate(['variables' => 'required|array']);
        if(Process::find($id_process)!=null){
            try{
                DB::beginTransaction();
                foreach( $request->input('variables.*') as $var ){
                    Log::debug($var['name']);
                    $pvar = ProcessVariable::where('name',$var['name'])
                            ->where('id_process',$id_process)
                            ->first();
                    if($pvar != null ){
                        $pvar->delete();
                    }
                }
                DB::commit();
            }catch(Exception $e){
                Log::error($e->getMessage());
                DB::rollback();
            }
            return response(null,200);
        }
        return response(null,404);
    }
    
}
