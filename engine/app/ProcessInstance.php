<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;


/**
 * Sec crea a partir de una definición Process
 */
class ProcessInstance extends Model{
    
    public function variables(){
        return $this->hasMany("App\ProcVarInstance","id_process_instance");
    }
    
    public function start(){
        //iniciar prerequisitos;
        //Se debe buscar la primera actividad asociada para crear una instancia
        $act_instance = Activity::where("process_id",$this->process_id)
                ->where("start_activity",1)
                ->first();
        $activity = $act_instance->newActivityInstance($this);
        do{
            $activity = $activity->next($this);
            
            //Actualizar la actividad actual
            if($activity == null){
                Log::debug('Actividad nula');
                $this->state="finish";
                return $this->exportVariables();
            }else{
                Log::debug("actividad -> ".$activity->id);
                $this->activityCursor = $activity->id; 
            }
        }while($activity != null);
        //iniciar postrequisitos;
    }
    
    public function exportVariables(){
        $variables = $this->variables()->select("name","value")->get();
        return array("variables" => $variables);
    }
}
