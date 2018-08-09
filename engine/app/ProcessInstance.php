<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;


/**
 * Sec crea a partir de una definición Process
 */
class ProcessInstance extends Model{
    
    public function variables(){
        $this->process()
                ->declaredVariables()
                ->join("process_var_instances",
                    "process_var_instances.id_process_var",
                        "process_variable.id")->all();
    }
    
    public function start(){
        //iniciar prerequisitos;
        //Se debe buscar la primera actividad asociada para crear una instancia
        $act_instance = Activity::where("process_id",$this->process_id)
                ->where("start_activity",1)
                ->first();
        $activity = $act_instance->newActivityInstance();
        do{
            $activity = $activity->next();
            
            //Actualizar la actividad actual
            if($activity == null){
                Log::debug('Actividad nula');
                $this->state="finish";
            }else{
                Log::debug("actividad -> ".$activity->id);
                $this->activityCursor = $activity->id; 
            }
        }while($activity != null);
        //iniciar postrequisitos;
    }
}
