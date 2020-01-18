<?php

namespace App\Providers;

use App\Models\User;
use App\Services\UserService;
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

                        $userBuilder = UserService::getUserBuilderForLogin();

                        /** @var User|null $user */
                        $user = $userBuilder->where('id', $userPayload['id'])->first();

                        return $user;
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
