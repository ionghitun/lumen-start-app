<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Http\ResponseFactory;

/**
 * Class CorsMiddleware
 *
 * @package App\Http\Middleware
 */
class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response|ResponseFactory|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        header('Access-Control-Allow-Origin: *');

        $headers = [
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, DELETE, PATCH',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Auth-Token, Origin, Authorization',
        ];

        if ($request->getMethod() == 'OPTIONS') {
            return response('OK', Response::HTTP_OK, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
