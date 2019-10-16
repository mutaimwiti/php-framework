<?php

use Core\Router\Router;

$router = new Router();

// controllers namespace: Controllers
$router->namespace('Controllers', function ($router) {
    // api group: api
    $router->group('api', function ($router) {
        // api controllers namespace: Controllers\API
        $router->namespace('API', function ($router) {
            // api v1 group: api/v1
            $router->group('v1', function ($router) {
                // v1 api controllers namespace: Controllers\API\V1
                $router->namespace('V1', function ($router) {
                    // uri: api/v1/users, action: Controllers\API\V1\UsersController@store
                    $router->post('users', 'UsersController@store');
                });
            });

            // api v2 group: api/v2
            $router->group('v2', function ($router) {
                // v1 api controllers namespace: Controllers\API\V2
                $router->namespace('V2', function ($router) {
                    // uri: api/v2/reports, action: Controllers\API\V2\ReportsController@store
                    $router->get('reports', 'ReportsController@index');
                });
            });

            // route => uri: api/info, action: Controllers\API\MasterController@info
            $router->get('info', 'MasterController@info');
        });
    });
});

return $router;
