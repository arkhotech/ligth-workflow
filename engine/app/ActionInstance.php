<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class ActionInstance extends Model
{
    //
    public function activityInstance(){
        return $this->belongsTo("App\ActivityInstance");
    }
    
    public function next(){
        $this->nextAction()->first();
    }
    
    private function create($type = "rest"){
        Log::info("actions.".$type);
        $source = Config::get("actions.".$type);
        Log::debug("Source: ".$source);
        $action = new $source($this->config);
        return $action;
    }
    
    public function execute(){
        Log::info("---------------------------------");
        Log::info("Ejecutando accion: [".$this->name."]");
        try{
            $action_imp = $this->create($this->class);
            $response = $action_imp->execute(null);
            $this->saveToVariable($response);
            Log::debug($response);
            Log::debug("Ejecutando actividad");
        }catch(Exception $e){
            $this->exception = array("error" => $e->getMessage());
            $this->action_status = Action::ACTION_ERROR;
        }finally{
            $this->save();
        } 
    }
    
    private function saveToVariable($value){
        if($value!=null){
            $this->output = $value;
        }
    }
    
}
