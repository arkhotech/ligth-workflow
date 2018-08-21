<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Process;
use App\ActivityRole;
use App\ProcessRole;
/*
 * const IDLE = 0;
    
    const ON_ENTRY=1;
    
    const ON_ACTIVITY = 2;
    
    const ON_EXIT = 3;
    
    const PENDDING = 4;
    
    const FINISHED = 5;
    
    const ERROR = 6;
 */


class TrayController extends Controller
{
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
