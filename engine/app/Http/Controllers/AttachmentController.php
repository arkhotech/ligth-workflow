<?php

namespace App\Http\Controllers;

use App\Attachment;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    public function upload(Request $request,$id_proc,$id_act){
        
        
        if($request->hasFile('file')){
            $file = $request->file('file');
            $attachment = new Attachment($file);
            $attachment->extension = $file->getClientOriginalExtension();
            $attachment->name = $file->getClientOriginalName();
            $attachment->activity_instance_id = $id_act;
            $attachment->process_instance_id = $id_proc;
            $attachment->mime_type = $file->getMimeType();
            $attachment->metadata = "{}";
            $attachment->description = "";
            $attachment->save();
            return response(null,201);
        }else{
            return response(null,419);
        }
    }
}
