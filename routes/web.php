<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Router;

/** CORS options route
 *
 * @var Router $router
 */
$router->options('/{any:.*}', ['middleware' => ['cors'], function () {
    return response('OK', Response::HTTP_OK);
}]);

/** Routes that doesn't require auth */
$router->group(['middleware' => 'cors'], function () use ($router) {
    /** Information about this API */
    $router->get('/', ['uses' => 'ApiController@version']);

    /** Users routes */
    $router->post('/login', ['uses' => 'UserController@login']);
    $router->post('/login-facebook', ['uses' => 'UserController@loginWithFacebook']);
    $router->post('/login-twitter', ['uses' => 'UserController@loginWithTwitter']);
    $router->post('/login-google', ['uses' => 'UserController@loginWithGoogle']);
    $router->post('/register', ['uses' => 'UserController@register']);
    $router->post('/forgot-password', ['uses' => 'UserController@forgotPassword']);
    $router->post('/change-password', ['uses' => 'UserController@changePassword']);
    $router->post('/activate-account', ['uses' => 'UserController@activateAccount']);
    $router->post('/resend-activation-code', ['uses' => 'UserController@resendActivationCode']);
    $router->post('/confirm-email', ['uses' => 'UserController@confirmEmail']);
});

/** Routes with auth */
$router->group(['middleware' => ['cors', 'auth']], function () use ($router) {
    /** Users routes */
    $router->post('/logout', ['uses' => 'UserController@logout']);
    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->get('', ['uses' => 'UserController@getUser']);
        $router->patch('', ['uses' => 'UserController@updateUser']);
        $router->post('/picture', ['uses' => 'UserController@changeUserPicture']);
    });

    /** Tasks routes */
    $router->post('/tasks', ['uses' => 'TaskController@getUserTasks']);
    $router->group(['prefix' => 'task'], function () use ($router) {
        $router->post('/', ['uses' => 'TaskController@createTask']);
        $router->group(['prefix' => '{$id}'], function () use ($router) {
            $router->get('', ['uses' => 'TaskController@getTask']);
            $router->patch('', ['uses' => 'TaskController@updateTask']);
            $router->delete('', ['uses' => 'TaskController@deleteTask']);
        });
    });
});
