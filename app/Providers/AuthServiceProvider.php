<?php

namespace App\Providers;

use App\Models\User;
use Exception;
use Illuminate\Support\ServiceProvider;
use IonGhitun\JwtToken\Jwt;

/**
 * Class AuthServiceProvider
 *
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
    }

    /**
     * Boot the authentication services for the application.
     */
    public function boot()
    {
        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->header('Authorization')) {
                $requestToken = explode(' ', $request->header('Authorization'));

                if (isset($requestToken[1])) {
                    try {
                        $userPayload = Jwt::validateToken($requestToken[1]);

                        return User::where('id', $userPayload['id'])->first();
                    } catch (Exception $e) {
                        return null;
                    }
                }

                return null;
            }

            return null;
        });
    }
}
