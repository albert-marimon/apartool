<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //Comprobar si el usuario está identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\jwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken){
            return $next($request);
        } else {
            //Mensaje de error
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'El usuario no está identificado.'
            );

            return response()->json($data, $data['code']);
        }
        
    }
}