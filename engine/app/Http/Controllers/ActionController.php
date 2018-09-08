<?php

namespace App\Http\Controllers;

use App\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\ActivityInstance;
use App\Activity;
use Events\ActivityEvents;
use App\Actions\LinkedListHelper;
use Illuminate\Support\Facades\Config;

class ActionController extends Controller
{
    public function registeredActions(){
        return response()->json(Config::get('actions'),200);
    }
    
    public function newAction(Request $request, $id_activity){
        
        $request->validate([ 
            "command" => "string",
            "config" => "array|required",
            "name" => "string|required|max:50",
            "action_type" => "required|string",
            "type" => [ "string" , 
                  Rule::in(["ON_ENTRY","ON_EXIT"])],]);
        $activity = Activity::find($id_activity);
        if($activity!=null){ 
            DB::beginTransaction();
            try{
                $action = new Action();
                $action->type = Action::getType($request->input('type'));
                $action->name = $request->input('name');
                $action->class = $request->input('action_type');
                $action->activity_id = $id_activity;
                $action->description = $request->input('description');
                $action->config = json_encode($request->input('config.*'));
                $action->save();
                $this->addToChain($activity,$action,$action->type);
                DB::commit();
                return response()->json(["action_id" => $action->id],201);
            }catch(Exception $e){
                DB::rollack();
                throw $e;
            }
        }
        return response(null,404);
    }
    
    private function addToChain($activity,$action,$type){
        $last_action = Action::where('activity_id',$activity->id)
                            ->whereNull('id_next_action')
                            ->whereType($type)
                            ->where('id','<>',$action->id)
                            ->first();
        if($last_action===null){
            Log::debug('No hay ninguna acción configurada');
            return;
        }
        $action->id_prev_action = $last_action->id;
        $last_action->id_next_action = $action->id;
        $last_action->save();
        $action->save();
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
            
            if( ! $this->moveUp($action)){
                return response()
                        ->json(["message" 
                            => "La acción esta en el primer lugar de la cadena"],304);
            }
            return response(null,200);
             
        }
        return response(null,404);
    }
    
}
