<?php

namespace App\Http\Controllers;

use App\Domain;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //$request->query('name');
        $query = Domain::query();
        foreach(Domain::fields as $field){
            $query_field = $request->query($field);
            if($query_field!=NULL){
                $query->where($field,$request->query($field));
            }
        }
        return response()->json($query->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $request->validate(
                ["name" => 'required|string',
                 "admin_email" => 'required|string|email',
                 "company_name" => 'required|string',   
                 "description" => 'string']);
        
        $domain = new Domain();
        $domain->name = $request->input('name');
        $domain->description = $request->input('description');
        $domain->admin_email = $request->input('admin_email');
        $domain->company_name= $request->input('company_name');
        $domain->save();
        return response()->json(["status" => "ok", "id" => $domain->id ],201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateDomain(Request $request,$id){
        $domain = Domain::find($id);        
        if($domain != null ){
            $updated = [];
            foreach(Domain::fields as $field){
                if(($value = $request->input($field))!=null){
                    $domain->{$field}=$value;
                    $updated[]=$field;
                }
            }
            $domain->save();
            return response()->json(["status" => "ok","updated_fields" => $updated]);
        }
        return response(null,404);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function deleteDomain($id){
        $domain = Domain::find($id);
        if($domain!=null){
            $domain->delete();
            return response(null,200);
        }
        return response(null,404);
    }
}
