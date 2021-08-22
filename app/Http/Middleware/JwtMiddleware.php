<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {

            // The token can be passed in the header or in the payload.
            JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {

            if ($e instanceof TokenInvalidException) {

                return response()->json(['status' => 'Token is Invalid']);
            }

            if ($e instanceof TokenExpiredException) {

                return response()->json(['status' => 'Token is Expired']);
            }

            return response()->json(['status' => 'Authorization Token not found']);
        }

        return $next($request);
    }

}
