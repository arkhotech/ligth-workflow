<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Http\Controllers;

use App\Parameter;
use Illuminate\Http\Request;
use App\ProcessInstance;
use App\ActivityInstance;
use App\StageInstance;
use App\Form;


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
        $stages = $activity_instance->stagesInstances()->first()->with('variables');
        return response()->json($stages);
    }
    
}
