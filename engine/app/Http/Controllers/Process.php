<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Process extends Controller
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
}
