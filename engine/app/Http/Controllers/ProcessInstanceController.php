<?php

namespace App\Http\Controllers;

use App\Process;

class ProcessInstanceController extends Controller{
   
    public function instances($id_proceso,$id=null){
        $retval = array();
        $process = Process::where("id",$id_proceso)->first();
        if($process!= null){
            
            $instancias = ($id==null) ? 
                    $process->instances()->get() :
                    $process->instances()->get()->where("id",$id);
            
            foreach($instancias as $instancia){
                $instancia->variables  = $instancia->variables()->select("name","value")->get();
                $retval[] = $instancia ;
            }
            return response()->json($retval,200);
        }
        return response(null,404);
        
}
   
}
