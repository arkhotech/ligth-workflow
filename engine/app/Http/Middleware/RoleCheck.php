<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RoleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$permited_role)
    {
        $user = Auth::user();
        if($user == null ){
            return response()->json(["message" => "Usuario no autorizado"],401);
        }
        $roles = explode(",",$permited_role);
        
        $role = $user->roles()->whereIn('name',$roles)->first();
        Log::debug($role);
        if( $role == null ){
            Log::warning("Intento de acceso prohibido desde: ".$request->ip());
            return response(null,403);
        }
        Log::debug("User: ".$user->name);
        return $next($request);
    }
}
