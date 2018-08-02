<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model implements EditableFieldsIF
{
    //
    use SoftDeletes;
    
    private $file;
    
    public function __construct($file=null) {
        if($file != null){
            $this->file = $file;
        }
    }
    

    public function fields(){
        return ['name','driver','extension','mime_type','url','metadata','description'];
    }
    
    public function save(){
        $path = env('TEMP_UPLOAD_DIR');
        parent::save();
    }
    
    public function read(){
        
    }
}
