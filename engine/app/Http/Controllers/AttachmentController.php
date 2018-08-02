<?php

namespace App\Http\Controllers;

use App\Attachment;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    public function upload(Request $request){
        
        
        if($request->hasFile('file')){
            $file = $request->file('file');
            $attachment = new Attachment($file);
            $attachment->extension = $file->getClientOriginalExtension();
            $attachment->name = $file->getClientOriginalName();
            $attachment->mime_type = $file->getMime();
            ;
        }
    }
}
