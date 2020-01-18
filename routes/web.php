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
    $router->post('/login', ['uses' => 'LoginController@login']);
    $router->post('/login-token', ['uses' => 'LoginController@loginWithRememberToken']);
    $router->post('/login-facebook', ['uses' => 'LoginController@loginWithFacebook']);
    $router->post('/login-google', ['uses' => 'LoginController@loginWithGoogle']);
    $router->post('/register', ['uses' => 'UserController@register']);
    $router->post('/forgot-password', ['uses' => 'UserController@forgotPassword']);
    $router->post('/change-password', ['uses' => 'UserController@changePassword']);
    $router->post('/activate-account', ['uses' => 'UserController@activateAccount']);
    $router->post('/resend-activation-code', ['uses' => 'UserController@resendActivationCode']);
});

/** Routes with auth */
$router->group(['middleware' => ['cors', 'auth']], function () use ($router) {
    /** Users routes */
    $router->post('/logout', ['uses' => 'LoginController@logout']);
    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->get('', ['uses' => 'UserController@getLoggedUser']);
        $router->patch('', ['uses' => 'UserController@updateLoggedUser']);
        $router->post('/picture', ['uses' => 'UserController@changeLoggedUserPicture']);
    });

    /** Tasks routes */
    $router->get('/tasks', ['uses' => 'TaskController@getUserTasks']);
    $router->group(['prefix' => 'task'], function () use ($router) {
        $router->post('/', ['uses' => 'TaskController@createTask']);
        $router->group(['prefix' => '{id}'], function () use ($router) {
            $router->get('', ['uses' => 'TaskController@getTask']);
            $router->patch('', ['uses' => 'TaskController@updateTask']);
            $router->delete('', ['uses' => 'TaskController@deleteTask']);
        });
    });
});
