<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use App\Domain;

class DomainCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$domain_admin)
    {
        //check los dominios que son de administracion
        Log::debug("Domain: ".$domain_admin);
        Log::debug( $request->header("DomainAuthorization"));
        
//        $domain = Domain::where("name",$request->header("Host"))->first();
//        if($domain == null ){
//            return response("Dominio ".$request->header("Host")." No registrado",401);
//        }
        return $next($request);
    }
}
