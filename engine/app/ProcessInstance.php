<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Sec crea a partir de una definición Process
 */
class ProcessInstance extends Model implements Events\ActivityEvents{

    public function variables() {
        return $this->hasMany("App\ProcVarInstance", "id_process_instance");
    }

    public function start() {
        
        $this->onEntry();
        $this->onActivity();
        $this->onExit();
       
    }

    public function exportVariables() {
        $variables = $this->variables()->select("name", "value")->get();
        return array("variables" => $variables);
    }

    public function onActivity() {
         //iniciar prerequisitos;
        //Se debe buscar la primera actividad asociada para crear una instancia
        $act_instance = Activity::where("process_id", $this->process_id)
                ->where("start_activity", 1)
                ->first();
        $activity = $act_instance->newActivityInstance($this);
        do {
            $activity = $activity->next($this);

            //Actualizar la actividad actual
            if ($activity == null) {
                Log::debug('Actividad nula');
                self::update(['state' => 'finish']);
                return $this->exportVariables();
            } else {
                Log::debug("actividad -> " . $activity->id);
                self::update([ "activityCursor" => $activity->id]);
            }
        } while ($activity != null);
        //iniciar postrequisitos;
    }

    public function onEntry() {
        
    }

    public function onExit() {
        
    }

}
