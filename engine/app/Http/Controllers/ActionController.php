<?php

namespace App\Http\Controllers;

use App\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\ActivityInstance;
use App\Activity;
use Events\ActivityEvents;
use App\Actions\LinkedListHelper;

class ActionController extends Controller
{
    public function newAction(Request $request, $id_activity, $prev_id = null){
        
        $request->validate(["type" => "string|required", 
            "command" => "string",
            "config" => "string"]);
        $activity = Activity::find($id_activity);
        if($activity!=null){ 
            DB::beginTransaction();
            try{
                $action = new Action();
                $action->type = Action::getType($request->input('type'));
                $action->id_activity = $id_activity;
                $action->save();

                if($prev_id != null){
                    $prev_action = Action::find($prev_id);
                    if($prev_action != null){
                        //Agregra el valor de la nueva referencia
                        $prev_action->id_next_action = $action->id;
                        $prev_action->save();
                        $action->id_prev_action = $prev_action->id;
                        $action->save();
                    }
                }
                
                DB::commit();
                return response()->json(["action_id" => $action->id],201);
            }catch(Exception $e){
                DB::rollack();
                throw $e;
            }
        }
        return response(null,404);
    }
    
    public function listActionsByActivity($id_activity){
        $activity = Activity::find($id_activity);
        if($activity != null){
            $result = $activity->actions()
                    ->select("id","command","type","created_at","id_next_action","id_prev_action")
                    ->get();
            return response()->json($result,200);
        }
        return response()->json(["No existe la actividad"],404);
    }
    
    public function listActionsChainsByActivity($id_activity){
        $activity = Activity::find($id_activity);
        if($activity != null){
            $result = array();
            $action = $activity->actions()
                            ->select("id","command","type","created_at","id_next_action","id_prev_action")
                            ->first();
            while($action){
                $next = $action->nextAction()->first();
                if($next!= null){
                    $action->next = $next;
                }
                $result[] = $action;
                $action = $next;
            }
            return response()->json($result,200);
        }
        return response(null,404);
    }
    
    public function removeAction($id){
        $action = Action::find($id);
        if($action != null ){
            $action->delete();
            return response(null,200);
        }
        return response(null,404);
    }
    //TODO revisar el movedown
    public function move($id_action,$sense = "moveup"){
        $action = Action::find($id_action);
        if( $action != null ){
            
            if( ! LinkedListHelper::moveUp($action)){
                return response()
                        ->json(["message" 
                            => "La acción esta en el primer lugar de la cadena"],304);
            }
            return response(null,200);
             
        }
        return response(null,404);
    }
    
}
