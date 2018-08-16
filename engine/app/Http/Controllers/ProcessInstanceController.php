<?php

namespace App\Http\Controllers;

use App\Events\Events;
use App\Process;
use \App\ProcessInstance;
use \App\ActivityInstance;

class ProcessInstanceController extends Controller{
   
    public function instances($id_proceso,$id=null){
        $retval = array();
        $process = Process::where("id",$id_proceso)->first();
        if($process!= null){
            
            $instancias = ($id==null) ? 
                    $process->instances()->get() :
                    $process->instances()->get()->where("id",$id);
            
            foreach($instancias as $instancia){
                $instancia->state = ProcessInstance::$STATES[$instancia->process_state];
                $instancia->variables  = $instancia->variables()->select("name","value")->get();
                $actual_activity = ActivityInstance::find($instancia->activityCursor);
                if($actual_activity!=null){
                    $actual_activity->state = 
                            ProcessInstance::$STATES[$actual_activity->activity_state];
                }
                $instancia->activity = $actual_activity;
                $retval[] = $instancia ;
            }
            return response()->json($retval,200);
        }
        return response(null,404);
        
}
   
}
