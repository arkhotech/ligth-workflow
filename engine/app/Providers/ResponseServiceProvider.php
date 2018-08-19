<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('badreq', function ($data) {
            return Response::json([
                "error" => true,
                'message'  => $data],400);
        
        });

        Response::macro('created', function ($data=null) {
            if($data==null){
                return Response::json(null,201);
            }else{
                return Response::json([
                    'error' => false,
                    'message'  => $data],201);
            }
        });
        Response::macro('nofound', function ($data=null) {
                return Response::json([
                        'error' =>  true,
                        'message'  => $data],404);
            
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
