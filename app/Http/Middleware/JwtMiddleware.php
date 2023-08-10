<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{

    protected $response = [];
    protected $error_msg = null;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){

                $this->error_msg = "Token is Invalid";

            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){

                $this->error_msg = "Token is Expired";

            }else{

                $this->error_msg = "Authorization Token not found";
            }

            $this->response = [
                "status" => "error",
                "error" => true,
                "response_code" => 400,
                "message" => $this->error_msg,
            ];

            return response($this->response, $this->response['response_code']);
        }
        return $next($request);
    }
}
