<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Process;

class TrayController extends Controller
{
    //
    public function listProcess(){
        $process = Process::all();
        return response()->json($process,200);
    }
    
    public function inputTask(){
        $roles = Auth::user()->roles();
        $arr = array();
        foreach($roles as $role){
            $arr[] = $role->id;
        }
        $activities = Activity::whereIn('role',$arr)->get();
        
    }
    
    
    public function instances(){
        
    }
}
