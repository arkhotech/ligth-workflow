<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Process;
use App\Domain;
use Illuminate\Support\Facades\DB;
use App\DeclaredVariable;

class ProcessController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request){
        //$this->middleware("auth:api");
    }
    
 
    
    public function listProcess($id_domain){
        //validar dominio
        if(Domain::find($id_domain)==null){
            return response()->json(array("status" => "error","message" => "dominio no existe"),404);
        }
       
        $process = Process::where("domain_id",$id_domain)->get();
        
        return response()->json($process,200);
    }
    
    public function newProcess(Request $request,$domain_id){
        $request->validate([
            'name' => 'required|string',
            'role_owner_id' => 'required|integer',
            'variables' => 'array'
        ]);
        DB::beginTransaction();
               
        //check Si existe:
        if(Process::where("name","=",$request->input('name'))
                ->where('role_owner_id',"=",$request->input('role_owner_id'))->first()!=null){
            return response()->json(['status' => 'registro existe'],409);
        }
        $process = new Process();
        
        foreach(Process::fields as $field){
            if($field == 'domain_id'){
                $process->domain_id = $domain_id; 
                continue;
            }
           $process->{$field} = $request->input($field);
        } 
        
        $process->save();
        $variables = $request->input('variables.*');
        foreach($variables as $item){
            $new_var = new ProcessVariable();
            $new_var->name = $item['name'];
            $new_var->process_id = $process->id;
            $new_var->type = $item['type'];
            $new_var->validator = $item['validator'];
            $new_var->save();
        }
        
        DB::commit();
        return response()->json(['id_process' => $process->id ],201);
        
    }
    
    public function deleteProcess($id){
        $process = Process::find($id);
        if($process){
            $process->delete();
        }else{
            return response(null,404);
        }
        return response(null,200);
    }
    
    public function listTrashedProcess($id_domain){
        $trashed =Process::onlyTrashed()->where('domain_id',$id_domain)->get();
        return response()->json($trashed);
    }
    
    public function restoreProcess($id_process){
        $process = Process::onlyTrashed()->find($id_process);
        if($process != null){
            $process->restore();
            return response()->json($process,201);
        }else{
            return response(null,404);
        }
    }
    
   
  
}
