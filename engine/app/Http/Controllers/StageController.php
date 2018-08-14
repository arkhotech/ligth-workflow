<?php

namespace App\Http\Controllers;

use App\Stage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StageController extends Controller
{
   public function newStage(Request $request, $id_activity, $id_prev=null,$id_next=null){
       $request->validate([
           "name" => "string|required",
           "type" => [ "string" , 
                  Rule::in(["FORM","CHOOSE"])],
           "descripcion" => "string|nullable",
           "next_stage" => "string|nullable",
           "prev_state" => "string|nullable"]);
       
       $stage = new Stage();
       foreach($stage->fields() as $field){
           
           $stage->{$field} = $this->mapType($field,$request->input($field));
       }
       $stage->activity_id = $id_activity;
       $stage->save();
       return response()->json(["id"=> $stage->id],201);
   }
   
   private function mapType($field,$value){
       
       if($field == "type" && key_exists(strtoupper($value), Stage::$TYPE)){
           return Stage::$TYPE[$value];
       }
       return $value;
   }
}
