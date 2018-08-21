<?php

namespace App\Http\Controllers;

use App\Stage;
use App\Activity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\ProcessInstance;

class StageController extends Controller{
    
    
    public function listStages($id_activity){
        $activity = Activity::find($id_activity);
        if($activity != null){
            $stages = $activity->stages()->get();
            $activity['stages'] = $stages;
            return response()->json($activity,200);
        }
        return response(null,404);
    }
    
    
   public function newStage(Request $request, $id_activity, $id_prev=null,$id_next=null){
       $request->validate([
           "name" => "string|required",
           "type" => [ "string" , 
                  Rule::in(["FORM","CHOOSE"])],
           "descripcion" => "string|nullable"
          ]);
       try{
            $stage = new Stage();
            // buscar el último stage
            $last_stage = Stage::where("activity_id",$id_activity)
                    ->where("next_stage")->first();
            foreach($stage->fields() as $field){
                Log::debug("[newStage] actializando campos : $field");
                $stage->{$field} = $this->mapType($field,$request->input($field));
            }
            $stage->activity_id = $id_activity;
            $stage->save();
            Log::debug("Registro guardado");
            if($last_stage != null){
                 $stage->prev_stage = $last_stage->id;
                 $last_stage->next_stage = $stage->id;
                 $stage->save();
                 $last_stage->save();
            }
       }catch(QueryException $e){
           Log::error($e->getCode());
           if($e->getCode()==23000){
                return response()->json(
                        array("message" => 
                            "Nombre e ID de actividad duplicado"),412);
           }else{
               return response()->json($e->getTrace(),500);
           }
       }
       return response()->json(["id"=> $stage->id],201);
   }
   
   private function updateForm(Request $request, $id_process_instance){
       return response(null,400);
   }
   
   private function mapType($field,$value){
       
       if($field == "type" && key_exists(strtoupper($value), Stage::$TYPE)){
           return Stage::$TYPE[$value];
       }
       return $value;
   }
}

