<?php

namespace App\Http\Middleware;

use App\Constants\TranslationCode;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\JsonResponse;

/**
 * Class Authenticate
 *
 * @package App\Http\Middleware
 */
class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var Auth
     */
    protected $auth;

    /**
     * Authenticate constructor.
     *
     * @param  Auth  $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param  Closure  $next
     * @param $guard
     *
     * @return JsonResponse|mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            $response = [
                'isError'       => true,
                'userFault'     => true,
                'errorMessages' => ['authorization' => TranslationCode::ERROR_UNAUTHORIZED]
            ];

            return response()->json($response);
        }

        return $next($request);
    }
}
