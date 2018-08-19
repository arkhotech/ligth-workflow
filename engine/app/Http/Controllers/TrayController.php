<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Process;
use App\ActivityRole;
use App\ProcessRole;

class TrayController extends Controller
{
    //
    public function listProcess(){
        $process = Process::all();
        return response()->json($process,200);
    }
    
    
    public function listTask(){
        $user = Auth::user();
        $roles = $user->roles()->select('id')->get();
        
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
