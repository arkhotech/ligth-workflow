<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Process;
use App\ActivityRole;
use App\Tray;
use App\Role;
/*
 * 
 */


class TrayController extends Controller
{
    public function listUserTrays(){
        $user = Auth::user();
        $id = 1;
        //Bandeja personal
        
//        Collection::macro('to_state', function () {
//            return $this->map(function ($value) {
//                
//                $value->each(function($item){
//                    switch($item->process_state){
//                        case 0: 
//                            $item->process_state = "IDLE";break;
//                        case 4:
//                           $item->process_state = "PENDDING";break; 
//                        case 5:
//                            $item->process_state = "FINISH";break;
//                        case 6: 
//                            $item->process_state = "ERROR";break;
//                    }
//                });
//                return $value;
//            });
//        });
        
        //Bandejas generales
        $trays = Tray::with('roles')
                ->select(['id','tray_name','description'])
                ->get();
        
        $trays->each(function($item,$key){
            $item->roles;
            
            $process = Role::join('process_roles','roles.id','=','process_roles.role_id')
                            ->join('processes','processes.id','=','process_roles.process_id')
                            ->join('process_instances','process_instances.process_id','=','processes.id')
                            ->whereIn('roles.id',$item->roles)
                            ->select("process_instances.id",
                                    "processes.name",
                                    "process_instances.activityCursor",
                                    "process_instances.process_state")
                            ->get();
            
            
            
            $item->process_instances = $process;
            
        });
//        var_dump($trays->first());die;
                        
        $result['trays'] = $trays->forget('roles');
        return response()->json($result);
    }
    
    //
    public function listProcess(){
        $process = Process::with(['processInstances'=> function($query){
           $query->selectRaw("id, process_id,state,"
                   . "case process_state "
                   . "when 0 then 'IDLE'"
                   . "when 1 then 'ON_ENTRY'"
                   . "when 2 then 'ON_ACTIVITY'"
                   . "when 3 then 'ON_EXIT'"
                   . "when 4 then 'PENDDING'"
                   . "when 5 then 'FINISHED'"
                   . "when 6 then 'ERROR'"
                   . " end as process_state");
            
        }])->with('roles')->select("id","name","asynch")
                ->get();
        $retval['process'] = $process;
        return response()->json($retval,200);
    }
    
    
    public function listTask(){
        $user = Auth::user();
        $roles = $user->roles()->select("id")->get();
        
        $p = Process::join('process_roles',
                'process_roles.process_id',
                '=',
                'processes.id')
                ->whereIn('process_roles.roles_id',$roles)
                ->with(['processInstances'=> function($query) use ($roles){
                        $query->with('activitiesInstances')->get();
                    }])->get();        
                
        return response()->json($p,200);
    }
    
    
    public function inputTask(){
        $roles = Auth::user()->roles();
        $arr = array();
        foreach($roles as $role){
            $arr[] = $role->id;
        }
        $activities = Activity::whereIn('role',$arr)->get();
        
    }
    
    public function asignedWork(){
        $user = Auth::user();
        $roles = $user->roles()->select("id")->get();
        //Listar todas las actividades asignadas al rol.
        $activities = ActivityRole::whereIn("roles_id",$roles)
                ->join("activities"
                        ,"activities.id",
                        "=","activity_roles.activity_id")->get();
        
        return response()->json($activities,200);
    }
    public function instances(){
        
    }
}
