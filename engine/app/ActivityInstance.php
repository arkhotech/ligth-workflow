<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Events\ActivityEvent;
use App\Exceptions\ActivityException;
use Illuminate\Support\Facades\Auth;
use App\Events\Executable;
use App\Events\Events;
use Exception;

class ActivityInstance extends Model implements Executable{
    
     public function __construct() {
        parent::__construct();
        $this->activity_state = Events::IDLE;
    }
    
    public function activity(){
        return $this->belongsTo("\App\Activity");
    }
    
    public function actionsInstances(){
        return $this->hasMany("App\ActionInstance");
    }
    
    public function stagesInstances(){
        return $this->hasMany("App\StageInstance","activity_instance_id");
    }
    
    public function currentStage(){
        return StageInstance::where('id',$this->current_stage);
    }
    /**
     *
     * @return type
     */
    public function variables(){
        return $this->hasMany("App\Variable");
    }
    
    public function globalVariables(){
        $process_instance = $this->process()->first();
        return $process_instance->variables()->get();
    }
    
    public function process(){
        return $this->belongsTo("App\ProcessInstance","process_instance_id"); 
    }
    /**
     * Trae las variables declaradas en el proecso
     * @return type
     */
    public function declaredVariables(){
        return $this->hasMany("App\Variable");
    }

    private function executeStages(){
        //Traere la definicion de stages. Y ejecutar el primer stage
        $activity = $this->activity()->first();
        $stage = $activity->stages()
                ->whereNull("prev_stage")
                ->first();

        if($stage != null ){
            //Ejecutar las etapas
            $stage_instance = $stage->newStageInstance($this);
            $this->current_stage = $stage_instance->id;
            $stage_instance->start();
            $this->activity_state = Events::PENDDING;
        }else{
            Log::warning("No hay stages");
            $this->activity_state = Events::ON_EXIT; //Para el siguiente evento
        }
        $this->save();
    }
    
    public function executeActivity(){
        $this->onEntry();
        $instance = $this->onActivity();
        if($instance == null){
            Log::info('LOPP finalizado');
        }else{
            Log::info("Retornando status : " . $this->activity_state);
        }
        return $instance;
    }
    
    public function init() {
        Log::debug("[ActivityInstance][init] ID: ".$this->id);
//Obtener el primer form  
        $current_action = $this->getRootAction(Events::ON_ENTRY);
        $this->executeActionChain($current_action);

        //Finalizado hay que llamar al avento correspondiente
        if($this->type == Activity::JOIN){
            $process_instance = $this->process()->first();
            $this->join($process_instance,$this->activity()->first());
        }
        
        event(new ActivityEvent($this,Events::ON_ACTIVITY));
    }

    public function start() {
        Log::debug("[ActivityInstance][start][$this->id]");
        $activity = $this->activity()->first();
        
        Log::debug('[ActivityInstance][start] Type: '.$this->type);
//#####  Ejeucion de stages
        Log::debug("[ActivityInstance][start] $activity->name Ejecutando stages");
        $this->executeStages();
        Log::debug('Tipo actividada: Activity');
        Log::debug("Estado; ". $this->activity_state);
        event(new ActivityEvent($this,Events::ON_EXIT));        
    }
    
    public function end() {
          //TODO revisar las salidas
        Log::debug("[onExit]");
        
        $current_action = $this->getRootAction(Events::ON_EXIT);
        $this->executeActionChain($current_action);
        Log::info("Finalizando actividad");
        $this->activity_state = Events::FINISHED;
        $this->save();
        
        if($this->end_activity){
            Log::info("Informando a proceso finalización de actividad");
            $process = $this->process()->first();
            $process->finalize();
        }
        
        if($this->type == Activity::FORK){
            $process_instance = $this->process()->first();
            $this->fork($process_instance,$this->activity()->first());
        }else{
            $this->findOutputTransitions();
        }
        event(new ActivityEvent($this,Events::FINISHED));
    }
    
    private function executeActionChain($root_action){
        $current_action = $root_action;
        while($current_action != null){
            $action_instance = $current_action->createActionInstance($this);
            $action_instance->execute($this->variables()->get());
            $current_action = $current_action->getNextNode();
        }
        
    }
    
    private function getRootAction($direction){
        $activity = $this->activity()->first();
        if($direction !== Events::ON_ENTRY && $direction !== Events::ON_EXIT){
            Log::error("Se esta intentando obtener un tipo de acciones que no existe");
            throw new ActivityException("Se esta intentando obtener un tipo de acciones que no existe");
        }
        $root_action = $activity->actions()
                ->whereNull("id_prev_action")
                ->where("type",$direction)
                ->first();
        return $root_action;
    }
    
    private function findOutputTransitions(){
        $process_instance = $this->process()->first();
        $activity = $this->activity()
                ->with('outputTransitions')
                ->first();
        $transitions = $activity->outputTransitions;
        if($transitions!== null){ 
            //Evaludar las condiciones
            $next_activity =  $this->evaluateTransitions($transitions,$process_instance);
            Log::debug("Procesando las siguientes transiciones");
            event(new ActivityEvent($next_activity,Events::NEW_INSTANCE));
        }else{
            Log::debug("Transicion nula. Se asume final del proceso");
            event(new ActivityEvent($this->process()->first(),Events::ON_EXIT));
        }
    }
    
    private function evaluateTransitions($transitions,$process_instance){
        if($transitions == null || count($transitions)==0){
            Log::info('No hay transiciones que evaluar');
            return null;
        }
        $posible_output = array();
        $default_output = null;
        foreach($transitions as $transition ){
            Log::debug("Evaluando condicion de transicion: ".$transition->name." default".$transition->default);
            if($transition->default){
                Log::debug("%%%  Entrada por defecto");
                $default_output = $transition;
            }
            if( $transition->evaluate($this)){
                Log::debug("Output candidato: ".$transition->name);
                $posible_output[] = $transition;
            }
            
        }

        $output = null;
        if(count($posible_output) === 0 && $default_output === null){
            throw new ActivityException("La actividad no tienen ninguna transición que "
                    . "cumpla con la condución o nunguna salida por defecto");
        }else  if(count($posible_output) == 0 ){
            Log::info("[Evaluate] ##### Tomando salida por defecto");
            $output = $default_output;
        }else if (count($posible_output) > 1){
            Log::warning("[Evaluate] ##### Desambiguando multiples salidas");
            $true_and_default = $this->checkDefault($posible_output);
            $output =  ($true_and_default !== null ) 
                    ? $true_and_default :    //Es verdadero y además es por defecto
                      $posible_output[0];   //Si no, se slecciona la primera opción
        }else{
            $output = $posible_output[0];
        }
        Log::info("[Transition OUTPUT] ######  tomando la salida: [$output->name]");
        $next_activity = Activity::find($output->next_activity_id);
        return $next_activity->newActivityInstance($process_instance,Auth::user());  //Se asigna el usuario actual
    }
    
     private function join(ProcessInstance $process_instance, Activity $activity ){
         Log::info("[JOIN] Cerrando un path: ".$this->flow_path_id);
        $metadata = $process_instance->meta_data;

        if($this->flow_path_id  === null){
            Log::error("Se esta usando un join y no hay inicio de bifucación");
            throw new ActivityException("Se esta usando un join y no hay inicio de bifucación");
        }
        //Eliminar del registro
        //TODO ver como registrar los path abiertos
        if( key_exists($this->flow_path_id, $metadata["flow_paths"])){
            unset($metadata["flow_paths"][$this->flow_path_id]);
            $process_instance->meta_data = $metadata;
            $process_instance->save();
        }
    }
    
    private function fork(ProcessInstance $process_instance, Activity $activity){
        Log::info("[FORK] Ejecuando bifurcacion");
        $transitions = $activity->outputTransitions()->get();
        $result = array();
        $paths = array();
        foreach($transitions as $transition){
            Log::info("[ActivityInstance][fork] Salida ======>  ".$transition->name);
            $next_def = Activity::find($transition->next_activity_id);
            $uuid = Transition::createPathId();
            $next_activity = $next_def->newActivityInstance($process_instance,Auth::user(),(String)$uuid);
            $result[] = $next_activity;
            $paths[] = (String)$uuid;
            event(new ActivityEvent($next_activity,Events::NEW_INSTANCE));
        }
        //Agregra informacion en process instance de los caminos iniciados
        $process_instance->meta_data = json_encode(["flow_paths" => $paths]);
        $process_instance->save();
        return $result;
    }
    
    private function checkDefault($transitions){
        foreach($transitions as $transition ){
            if($transition->default){
                return $transition;
            }
        }
        return null;
    }

    public function handleError(Exception $e) {
        Log::info("Controlando error");
    }

}
