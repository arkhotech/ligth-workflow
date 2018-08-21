<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProcessInstance;
use App\ActivityInstance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessControlController extends Controller{
    /**
     * 
     * @param type $proc_inst_id
     * @return type
     * 
      [ "id", "name", "value", 
        "type", "validation", "description",
        "enabled",
        "output_field",
         "readOnly",
         "required"]
     */
    
    protected $outputFieldsNames = [ 
         "fields.id","field_instances.id","fields.name","form_instance_id", "fields.value", 
        "type", "validation", "description",
        "enabled",
        "output_field",
         "readOnly",
         "required"];
    
    
    public function currentForm($proc_inst_id){
        $pi = ProcessInstance::find($proc_inst_id);
        if($pi!= null ){
            Log::debug("Cursor: ".$pi->activityCursor);
            $current_activity = $pi->currentActivityInstance();

            Log::info("Current Activity ID: ".$current_activity->id);
            Log::info("Current Stage ID: ".$current_activity->stage);
            $current_stage =$current_activity->stagesInstances()
                    ->where('id',$current_activity->stage)
                    ->first();
            Log::info("Current Stage Instance: ".$current_stage->id);
            if($current_stage == null ){
                return response()
                        ->nofound("No hay stages instances para el Activity:"
                                .$proc_inst_id);
            }
            
            $form = $current_stage->formInstances()
                    ->select("id","stage_instance_id","form_id")
                    ->with(['fields' => function($query){
                        $query->select($this->outputFieldsNames)
                                ->join('fields',"fields.id",
                                        "=","field_instances.field_id");
                    }])
                    ->first();
            if($form == null){
                $form = $current_stage->createFormInstance();
            }
            return response()->json($form,200);
        }
        return response(null,404);
    }
    /**
     * Retornael siguiente formualario, con la entrada del anterior
     * @param Request $request
     * @param type $id_act_inst
     * @return type
     */
    public function submitForm(Request $request, $id_act_inst){
        
        $activity_instance = ActivityInstance::find($id_act_inst);
        if($activity_instance == null ){
            return response()->nofound("La instancia de actividad $id_act_inst. no existe");
        }
        try{
            DB::beginTransaction();
            $current_stage = $activity_instance->currentStage()->first(); 
            $current_form_instance = $current_stage->formInstances()->first();
            $result =$current_form_instance
                    ->injectInputVariables($this
                            ->createVarlist($request
                                    ->input('fields.*')));
            if( count($result) > 0){
                Log::info("Formulario con error en las validaciones");
                DB::rollback();
                return response()->json($result,400);
            }
            DB::commit();
            //Acá debería retornar el resultado de la validación.
            $result['output'] = $current_form_instance->execute();
            //Recuperar el próximo formulario. Responsabilidad de Stage.
            $next_stage = $current_stage->nextStage();
            $result['next_form']=$next_stage->formInstances()->with('fields')->first();  //CurrentForm
            //recuperar las variables
            return response()->json($result,200);
        }catch(Exception $e){
            Log::error("Error: ".$e->getMessage());
            DB::rollback();
            return response()->json(json_decode($e->getMessage()),500);
        }

    }
    
    public function createVarlist(array $params){
        $varlist = array();
        foreach($params as $param){
            $varlist[$param['name']] = $param['value']; 
        }
        return $varlist;
    }
    
}
