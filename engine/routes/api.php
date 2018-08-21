<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('test',"Test@test");

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
  
    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
        
    });
});

Route::group(
        ["prefix" => "domain"],
        function(){
            Route::post('/','DomainController@create');
            Route::get('/','DomainController@index');
            Route::put('/{id}','DomainController@updateDomain');
            Route::delete('/{id}','DomainController@deleteDomain');
        });

Route::group(
        ["prefix" => "process",
         "middleware" => "auth:api",
         "middleware" => "domain:admin"],
        function(){
            Route::get('/variables/{id_process?}','ProcessController@listVariables');
            Route::get('/from/domain/{id_domain}', 'ProcessController@listProcess');
            Route::get('/trash/{id_domain}', 'ProcessController@listTrashedProcess');
            Route::get('/{id_instance}/form',"StageController@actualForm"); //Obtener el formulario actual
            Route::get('/{id_proceso}/instances/{id?}','ProcessInstanceController@instances');
            Route::patch('/trash/{id_process}','ProcessController@restoreProcess');
            Route::post('/{id_domain}','ProcessController@newProcess');
            Route::put('/{id}','ProcessController@updateProcess');
            Route::delete('/{id}','ProcessController@deleteProcess');
            Route::post('/start/{id}','ProcessController@startProcess');
            
            Route::post('compiler',"CompileProcessController@compile");
            
            Route::post('/{id_instance}/form',"StageController@updateForm");  //Actualizar el formaulario actual
            
            Route::get('/{id_proceso}/activity/','ActivityController@listActivities');
            Route::post('/{id_proceso}/activity/','ActivityController@newActivity');
            
            Route::put('/{id_proceso}/activity/{id}','ActivityController@editActivity');
            
        });

Route::group(
    ["prefix" => 'activity',
     "middleware" => "auth:api",
     "middleware" => "roles:admin"],
        function(){
            Route::post('/{id_prev}/start','ActivityInstanceController@start');
            Route::get('/instance/{id_instance}/{sense}/transitions',"ActivityInstanceController@listTransitions");
//            Route::post('/transition_to/{id_next}',"TransitionController@createTransition");
            Route::get('/{id_activity}/transitions',"ActivityInstanceController@getTransitions");
            Route::get("{id_activity}/instances","ActivityInstanceController@listInstances");       
            Route::get("{id_activity}/actions","ActionController@listActionsByActivity");
            Route::get("{id_activity}/actions/chain","ActionController@listActionsChainsByActivity");
            
            Route::post("/{id}/stage","StageController@newStage");
            Route::post("/{id}/stage/prev/{id_prev?}/next/{id_next?}","StageController@newStage");
            Route::get("/{id}/stage","StageController@listStages");
            Route::delete('/{id}','ActivityController@deleteActivity');
            
        });        

        
Route::group(
        ["prefix"=> "transition",
         "middleware" => "auth:api",
         "middleware" => "roles:admin"],
        function(){
            Route::post("/from/{from_id}/to/{to_id}","TransitionController@createTransition");
            Route::get("/list/fromprocess/{id_process}","TransitionController@listTransitions");
            Route::patch("/{id_transition}/from/{from_id}/to/{to_id}","TransintionController@editTransition");
        });
        
Route::group(
    ["prefix" => "process/{id_proceso}/activity/{id_activity}/upload",
     "middleware" => "auth:api",
     "middleware" => "roles:admin,users"],
        function(){
            Route::post("/","AttachmentController@upload");
        });
        
        
Route::group(
        ["prefix" => "variables",
         "middleware" => "auth:api",
         "middleware" => "roles:admin"],
        function(){
            Route::get("/process/{id_process}","DeclaredVariableController@listProcessVariables");
            Route::get("/activity/{id_activity?}","DeclaredVariableController@listActivityVariables");
            Route::post("/process/{id_process}","DeclaredVariableController@addGlobalVariables");
            Route::put("/process/{id_process}","DeclaredVariableController@addGlobalVariables");
            Route::delete("/process/{id_process}","DeclaredVariableController@removeGlobalVariables");
        });
     
        
Route::group(
        ["prefix" => "actions",
         "middleware" => "auth:api",
         "middleware" => "roles:admin"],
        function(){
            Route::post("/activity/{id}/{id_prev_action?}","ActionController@newAction");
            Route::delete('/{id}','ActionController@removeAction');
            Route::put('/{id}/{sense}','ActionController@move');
        });
        
        
Route::group(
        ["prefix" => "tray",
         "middleware" => "auth:api",
         "middleware" => "roles:admin,users"],
        function(){
           Route::get("tasks","TrayController@listTask");   
           Route::get("process","TrayController@listProcess"); 
           Route::get("assigned","TrayController@asignedWork");
        });
        
Route::group(
        ["prefix" => "stage",
         "middleware" => "auth:api",
         "middleware" => "roles:admin"],
        function(){
            Route::delete("/{id}","StageController@deleteStage");
            Route::put("/{id}","StageController@editStage");
            Route::patch("/{id}/{sense}","StageController@mode");
            Route::post("/{id}/form","FormController@newForm");
            Route::get("{id}/form","FormController@listForms");
            Route::delete("{id}/form/{form_id}","FormController@deleteForm");
        });
        
Route::group(
        ["prefix" => "form/{id}"],
        function(){
            Route::patch("field/{id_field}/{sense}","FormController@fieldMove");
        });
        
        
Route::group(
        ["prefix" => "control",
         "middleware" => "auth:api",
         "middleware" => "roles:admin"],
        function(){
            Route::get("/process/instance/{id}/form","ProcessControlController@currentForm");
            Route::post("/process/instance/{id}/form/next","ProcessControlController@submitForm");
        });