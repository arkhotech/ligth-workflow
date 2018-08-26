<?php

namespace App\Http\Controllers;

use App\Activity;
use App\Process;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\User;
use App\Role;
use App\ActivityRole;

class ActivityController extends Controller{
    
    
    private function asignActivityRoles($roles,Activity $activity){
         if(count($roles)==0){ //Asginar roles por defecto
             Log::info("Asignando rol por defecto");
            $role = new ActivityRole();
            $role->activity_id = $activity->id;
            $role->role_id = Role::ADMIN_ROLE_ID;
            $role->save();
         }else{
            foreach($roles as $role_name){
               $role = Role::where("name",$role_name)->first();
               if($role == null ){
                   DB::rollback();
                   return response()->badreq("El rol no existe: $role_name");
               }
               $act_role = new ActivityRole();
               $act_role->role_id = $role->id;
               $act_role->activity_id = $activity->id;
               $act_role->save();
            }
         }
        
    }
    /**
     * 
     * @param Request $request
     * @param type $id_proceso
     * @return type
     */
    public function newActivity(Request $request,$id_proceso){
        $request->validate(
                ["name" => "required|string",
                 "type" => [ "required","string",Rule::in(["activity","conditional"])],
                 "roles" => "array|nullable"]
                );
        
        try{
            DB::beginTransaction();

            $activity = new Activity();
            if( Process::find($id_proceso) != null ){  
                //Check si es que existe el proceso para parear
                foreach($activity->fields() as $field){
                    $activity->{$field} = $request->input($field);
                }
                $activity->process_id = $id_proceso;
                $activity->save();
                $roles = ( $request->input("roles.*") != null ) 
                        ? $request->input("roles.*") : 
                    array();
                $this->asignActivityRoles($roles,$activity);
                DB::commit();
                return response()->json([ "activity_id" => $activity->id ], 201);
            }
        }catch(Exception $e){
            Log::error($e->getMessage());
            DB::rollback();
            return response(null,500);
        }
        return response()->json(array("message" => "No existe el proceso"),412);
    }
    
    public function listActivities($id_proceso){
        $process = Process::find($id_proceso);
        if($process == null){
            return response()->json(array("message" => "No existe el proceso"),412);
        }
        $activities = $process->activities()->get();
        foreach($activities as $activity){
            $activity->roles = $activity->roles()->select('id','name')->get();
        }
        return response()->json($activities);      
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
            foreach($activity->fields() as $field ){
                if($request->input($field)==null){
                    continue;
                }
                $activity->{$field} = $request->input($field);
            }
            $this->asignActivityRoles($request->input("roles.*"), $activity);
            $activity->save();
            return response(null,200);
        }
        return response()->json(array("message" => "No existe el proceso"),412);
      
    }
    
    public function deleteActivity( $id_activity){
        $activity = Activity::find($id_activity);
        if($activity!= null){
            $activity->delete();
            return response(null,200);
        }
        response()->json(array("message" => "No existe el proceso"),412);
    }
    
}
