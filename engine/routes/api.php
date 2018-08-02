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
         'middleware' => 'auth:api'],
        function(){
            Route::get('/{id_domain}', 'ProcessController@listProcess');
            Route::get('/trash/{id_domain}', 'ProcessController@listTrashedProcess');
            Route::patch('/trash/{id_process}','ProcessController@restoreProcess');
            Route::post('/{id_domain}','ProcessController@newProcess');
            Route::put('/{id}','ProcessController@updateProcess');
            Route::delete('/{id}','ProcessController@deleteProcess');
            Route::post('/start/{id}','ProcessController@startProcess');
            Route::get('/instances/{id_dominio}/{id_proceso}','ProcessController@instances');
            
        });


Route::group(
        ["prefix" => "process/{id_proceso}/activity",
            'middleware' => 'auth:api'],
        function(){
            Route::get('/','ActivityController@listActivities');
            Route::post('/','ActivityController@newActivity');
            Route::delete('/{id}','ActivityController@deleteActivity');
            Route::put('/{id}','ActivityController@editActivity');
        });

Route::group(
    ["prefix" => "process/{id_proceso}/activity/{id_activity}"],
        function(){
            
        });
        
        
Route::group(
    ["prefix" => "process/{id_proceso}/activity/{id_activity}/upload"],
        function(){
            Route::post("/","AttachmentController@upload");
        });