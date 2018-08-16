<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Form;
use App\Field;
use App\Stage;
use App\Actions\LinkedListHelper;

class FormController extends Controller
{
    //
    public function newForm(Request $request,$id_stage){
        $request->validate(["name" => "string|required"]);
        
        try{
            DB::beginTransaction();

            $form = new Form();
            $form->name = $request->input('name');
            $form->description = $request->input('description');
            $form->stage_id = $id_stage;
            $form->save();
            //Campos
            $id_prev = null;
            $prev_field = null;
            foreach($request->input('fields.*') as $field){
                
                $ffield = new Field();
                $ffield->name = $field['name'];
                $ffield->description = $field['description'];
                $ffield->type = $field['type'];
                $ffield->value= $field['value'];
                $ffield->form_id = $form->id;
                $ffield->prev_field = $id_prev;
                $ffield->save();
                $id_prev = $ffield->id;
                if($prev_field != null ){
                    $prev_field->next_field = $ffield->id;
                    $prev_field->save();
                }
                $prev_field = $ffield;
            }
            DB::commit();
            return response(null,201);
        }catch(Exception $e){
            DB::rollback();
            return response()->json($e->getMessage(),500);
        }
    }
    
    public function listForms($id_stage){
        
        $stage = Stage::find($id_stage);
        $retval = array();
        if($stage != null){
            $forms = $stage->forms()->get();
            foreach($forms as $form){
                $form->fields = $form->fields()->orderBy('prev_field','asc')->get();
                $retval[] = $form;
            }
            
            return response()->json($retval,200);
        }
        return response(null,404);
    }
    
    public function deleteForm($id_stage,$id_form){
        $stage = Stage::find($id_stage);
        if($stage != null ){
            $form = Form::find($id_form);
            if($form!= null){
                $form->delete();
                return response(null,200);
            }
             return response()
                ->json(["message"
                    =>"form no $id_form No existe"],404);
        }
        return response()
                ->json(["message"
                    =>"Stage no $id_stage No existe"],404);
    }
    
    public function fieldMove($id_form,$id_field,$sense='moveup'){
        $form = Form::find($id_form);
        if($form != null){
            $field = $form->fields()
                    ->where("id",$id_field)
                    ->first();
            if($field==null){
                return response()->json(['message' => "No se encuentra el campo"],404);
            }
            if($sense == 'moveup'){
                LinkedListHelper::moveUp($field);
            }else{
                Log::debug("movedown");
                LinkedListHelper::moveDown($field);
            }
            return response(null,200);
        }
        return response()->json(["message" => "Formulario $id_form no existe"],404);
    }
}
