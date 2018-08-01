<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Process;

class ProcessController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $this->middleware("auth:api");
    }
    
    public function listProcess(){
        return response()->json(array("status" => "ok"));
    }
    
    public function newProcess(Request $request){
        $request->validate([
            'name' => 'required|string',
            "domain_id" => 'required|integer',
            'role_owner_id' => 'required|integer'
        ]);
        //check Si existe:
        if(Process::where("name = ? and domain_id = ?", 
                $request->input('name'),
                $request->input('role_owner_id'))!=null){
            return response()->json(['status' => 'registro existe'],409);
        }
        $process = new Process();
        
        foreach(Process::fields as $field){
           $process->{$field} = $request->input($field);
        } 
        
        $process->save();
        
        return response()->json(['status' => 'ok'],201);
        
    }
    
    public function deleteProcess($id){
        
    }
    
    public function updateProcess(){
        
    }
}
