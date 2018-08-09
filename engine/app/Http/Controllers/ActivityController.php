<?php

namespace App\Http\Controllers;

use App\Activity;
use App\Process;
use Illuminate\Http\Request;

class ActivityController extends Controller{
    
    public function newActivity(Request $request,$id_proceso){
        $request->validate(
                ["name" => "required|string"]
                );
        $activity = new Activity();
        if( Process::find($id_proceso) != null ){  
            //Check si es que existe el proceso para parear
            foreach($activity->fields() as $field){
                $activity->{$field} = $request->input($field);
            }
            $activity->process_id = $id_proceso;
            $activity->save();
            return response(null,201);
        }
        return response()->json(array("message" => "No existe el proceso"),412);
    }
    
    public function listActivities($id_proceso){
        $process = Process::find($id_proceso);
        if($process == null){
            return response()->json(array("message" => "No existe el proceso"),412);
        }
        return response()->json($process->activities()->get());      
    }
    
    public function editActivity(Request $request,$id_proceso, $id_activity){
        
        $process = Process::find($id_proceso);
        if($process != null){
            $activity = $process->activities()
                    ->where('id',$id_activity)
                    ->first();
            if($activity == null){
                return response(null,404);
            }
            foreach(Activity::editable_fields as $field ){
                if($request->input($field)==null){
                    continue;
                }
                $activity->{$field} = $request->input($field);
            }
            $activity->save();
            return response(null,200);
        }
        return response()->json(array("message" => "No existe el proceso"),412);
      
    }
    
    public function deleteActivity($id_proceso, $id_activity){
        $process = Process::find($id_proceso);
        if($process != null){
            $activity = $process->activites()
                    ->where('id',$id_activity)
                    ->first();
            if($activity == null ){
                return response(null,404);
            }
        }
        response()->json(array("message" => "No existe el proceso"),412);
    }
    
}
