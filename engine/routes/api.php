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
            "miffleware" => "auth:api"],
        function(){
            Route::get('/variables/{id_process?}','ProcessController@listVariables');
            Route::get('/{id_domain}', 'ProcessController@listProcess');
            Route::get('/trash/{id_domain}', 'ProcessController@listTrashedProcess');
            Route::patch('/trash/{id_process}','ProcessController@restoreProcess');
            Route::post('/{id_domain}','ProcessController@newProcess');
            Route::put('/{id}','ProcessController@updateProcess');
            Route::delete('/{id}','ProcessController@deleteProcess');
            Route::post('/start/{id}','ProcessController@createInstance');
            Route::get('/instances/{id_proceso}','ProcessInstanceController@instances');
            
            Route::post('compiler',"CompileProcessController@compile");
            
        });


Route::group(
        ["prefix" => "process/{id_proceso}/activity"],
        function(){
            Route::get('/','ActivityController@listActivities');
            Route::post('/','ActivityController@newActivity');
            Route::delete('/{id}','ActivityController@deleteActivity');
            Route::put('/{id}','ActivityController@editActivity');
        });



Route::group(
    ["prefix" => 'activity'],
        function(){
            Route::post('/{id_prev}/start','ActivityInstanceController@start');
            Route::get('/instance/{id_instance}/{sense}/transitions',"ActivityInstanceController@listTransitions");
//            Route::post('/transition_to/{id_next}',"TransitionController@createTransition");
            Route::get('/{id_activity}/transitions',"ActivityInstanceController@getTransitions");
            Route::get("{id_activity}/instances","ActivityInstanceController@listInstances");       
           
        });        

        
Route::group(
        ["prefix"=> "transition"],
        function(){
            Route::post("/from/{from_id}/to/{to_id}","TransitionController@createTransition");
            Route::get("/list/fromprocess/{id_process}","TransitionController@listTransitions");
            Route::patch("/{id_transition}/from/{from_id}/to/{to_id}","TransintionController@editTransition");
        });
        
Route::group(
    ["prefix" => "process/{id_proceso}/activity/{id_activity}/upload"],
        function(){
            Route::post("/","AttachmentController@upload");
        });
        
        
Route::group(
        ["prefix" => "variables"],
        function(){
            Route::get("/process/{id_process}","DeclaredVariableController@listProcessVariables");
            Route::get("/activity/{id_activity?}","DeclaredVariableController@listActivityVariables");
            Route::post("/process/{id_process}","DeclaredVariableController@addGlobalVariables");
            Route::put("/process/{id_process}","DeclaredVariableController@addGlobalVariables");
            Route::delete("/process/{id_process}","DeclaredVariableController@removeGlobalVariables");
        });
        