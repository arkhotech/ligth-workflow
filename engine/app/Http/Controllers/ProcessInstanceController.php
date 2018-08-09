<?php

namespace App\Http\Controllers;

use App\ProcessInstance;
use App\Process;
use Illuminate\Http\Request;
use App\ProcessVariable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class ProcessInstanceController extends Controller
{
    

    
    public function instances($id_proceso){
        $process = Process::where("id",$id_proceso)->first();
        if($process!= null){
            return response()->json($process->instances()->get());
        }
        return response(null,404);
        
    }
   
}
