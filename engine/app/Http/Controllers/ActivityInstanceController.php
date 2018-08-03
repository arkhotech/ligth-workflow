<?php

namespace App\Http\Controllers;

use App\ActivityInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Activity;
use App\ActivityParameter;

class ActivityInstanceController extends Controller{
    
   public function start(Request $request,$id_process,$id_activity){
       
       $request->validate(['parameters' => 'array']);
 
       $activity = Activity::find($id_activity);
       if($activity != null ){
           try{
                DB::beginTransaction();
                $instance = $activity->newActivityInstance();
                foreach(Input::get('parameters') as   $item){
                    $param = new ActivityParameter();
                    $param->name = key($item);
                    $param->value = json_encode($item[key($item)]);
                    $param->id_activity = $id_activity;
                    $param->save();
                }
             DB::commit();
             return response(null,201);
           }catch(Throwable $e){
               DB::rollback();
               return response(null,500);
           }
       }
       return response(null,404);
   }
}
