<?php

namespace App\Http\Controllers;

use App\Transition;
use Illuminate\Http\Request;
use App\Activity;

class TransitionController extends Controller
{
    public function createTransition(Request $request, $prev_id,$next_id){
        $request->validate(["name" => "required|string",
            "condition" => "required|string"]);
        
        $prev_act = Activity::find($prev_id);
        $next_act = Activity::find($next_id);
        
        if($prev_act == null){
           return response()->json(['message' => 'Proceso Previo no encontrado'],404); 
        }
        
        if($next_act == null){
           return response()->json(['message' => 'Proceso siguiente no encontrado'],404); 
        }
        
        $transition = new Transition();
        $transition->name = $request->input('name');
        $transition->description = $request->input('description');
        $transition->prev_activity_id = $prev_id;
        $transition->next_activity_id = $next_id;
        $transition->condition = $request->input('condition');
        
        $transition->save();
        return response(null,201);        
    }
}
