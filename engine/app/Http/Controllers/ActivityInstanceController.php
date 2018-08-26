<?php

namespace App\Http\Controllers;

use App\ActivityInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivityInstanceController extends Controller{
   
   public function listInstances(Request $request, $id_activity){
       $instances = ActivityInstance::where('activity_id',$id_activity)->get();
       if($instances == null ){
           return response(null,404);
       }
       return response()->json($instances,200);
   }
   
   public function listTransitions($id_instance,$sense){
       $instance = ActivityInstance::where("id",$id_instance)->first();
       Log::debug('Sensito: '.$sense);
       if($sense != 'output' && $sense != 'input' ){
           return response()->json(array('message'=> "El sentido solo puede ser 'input' u 'output'"),400);
       }
       if($instance == null ){
           return response(null,404);
       }
       $activity = $instance->activity()->first();
       $transitions = [];
       if($sense == 'input'){
           $transitions = $activity->inputTransitions()->get();
       }else{
           $transitions = $activity->outputTransitions()->get();
       }
       return response()->json($transitions,200);
   }
   
   
}
