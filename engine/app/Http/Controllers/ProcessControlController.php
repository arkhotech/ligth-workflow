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
    
    
    public function getActualForm($proc_inst_id){
        $pi = ProcessInstance::find($proc_inst_id);
        if($pi!= null ){
            $act_instances = $pi->activitiesInstances()
                    ->where('id',$pi->activityCursor)
                    ->first();
            
            $stg =$act_instances->stagesInstances()
                    ->where('id',$act_instances->stage)
                    ->first();
            if($stg == null ){
                return response()
                        ->nofound("No hay stages instances para el Activity:"
                                .$proc_inst_id);
            }
            $form = $stg->formInstances()->with('fields')->first();
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
    public function nextForm(Request $request, $id_act_inst){
       
        $activity_instance = ActivityInstance::find($id_act_inst);
        if($activity_instance == null ){
            return response()->nofound("La instancia de actividad $id_act_inst. no existe");
        }
        try{
            DB::beginTransaction();
            $stage_instance = $activity_instance->actualStage()->first(); //->formInstance()->first();
            $form_instance = $stage_instance->formInstances()->first();
            $form_instance
                    ->inputVariables($this
                            ->createVarlist($request
                                    ->input('fields.*')));
            DB::commit();
            //recuperar las variables
            $form_instance = $stage_instance
                    ->formInstances()
                    ->with('fields')
                    ->first();
            //Event. Next form
            $form_instance->nextForm();
        }catch(Exception $e){
            Log::error("Error: ".$e->getMessage());
            DB::rollback();
            return response()->json(json_decode($e->getMessage()),500);
        }
        return response()->json($form_instance,200);
    }
    
    public function createVarlist(array $params){
        $varlist = array();
        foreach($params as $param){
            $varlist[$param['name']] = $param['value']; 
        }
        return $varlist;
    }
    
}
