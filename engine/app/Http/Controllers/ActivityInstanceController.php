<?php

namespace App\Http\Controllers;

use App\ActivityInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Activity;
use App\ActivityVariable;

class ActivityInstanceController extends Controller{
    
   public function start(Request $request,$id_activity){
       
       $request->validate(['parameters' => 'array']);
 
       $activity = Activity::find($id_activity);
       if($activity != null ){
           try{
                DB::beginTransaction();
                $instance = $activity->newActivityInstance();
                
                foreach(Input::get('parameters') as   $item){
                    $param = new ActivityVariable();
                    $param->name = key($item);
                    $param->value = json_encode($item[key($item)]);
                    $param->id_activity = $id_activity;
                    $param->save();
                }
                DB::commit();
                return response()->json(array("id_instance" => $instance->id),201);
           }catch(Throwable $e){
               DB::rollback();
               return response(null,500);
           }
       }
       return response(null,404);
   }
   
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
