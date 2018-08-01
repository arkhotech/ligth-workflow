<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
            "domain_id" => 'required|integer'
        ]);
        
        $process = new Process();
        
        foreach(Process::fields as $field){
           $process->{$field} = $request->input($field);
        }
        
        return response()->json(['status' => 'ok'],201);
        
    }
    
    public function deleteProcess($id){
        
    }
    
    public function updateProcess(){
        
    }
}
