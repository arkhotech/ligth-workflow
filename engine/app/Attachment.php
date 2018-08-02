<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \App\EditableFieldsIF;

class Attachment extends Model
{
    //
    use SoftDeletes;
    
    private $file;
    
    private $driver = "fs";
    
    public function __construct($file=null) {
        if($file != null){
            $this->file = $file;
        }
    }
    

    public function fields(){
        return ['name','driver','extension','mime_type','url','metadata','description'];
    }
    
    public function read(){
        
    }
    
    public function save(Array $options = array()){
        if($this->file!=null){
            if($this->driver === 'fs'){
 
                $this->url = "file:///app/upload".$this->name;
            }
            $this->file->move('/app/upload/',$this->name);
        }
        parent::save($options);
    }
}
