<?php

use Framework\Router\Router;

$router = new Router();

// app namespace: App, admin prefix: admin
$router->group(['namespace' => 'App', 'prefix' => 'admin'], function ($router) {
// controllers namespace: App\Controllers
    $router->namespace('Controllers', function ($router) {
        // api prefix: admin/api
        $router->prefix('api', function ($router) {
            // api controllers namespace: App\Controllers\API
            $router->namespace('API', function ($router) {
                // api v1 prefix: admin/api/v1
                $router->prefix('v1', function ($router) {
                    // v1 api controllers namespace: App\Controllers\API\V1
                    $router->namespace('V1', function ($router) {
                        // uri: admin/api/v1/users, action: App\Controllers\API\V1\UsersController@store
                        $router->post('users', 'UsersController@store');
                    });
                });

                // api v2 prefix: admin/api/v2
                $router->prefix('v2', function ($router) {
                    // v1 api controllers namespace: App\Controllers\API\V2
                    $router->namespace('V2', function ($router) {
                        // uri: admin/api/v2/reports, action: App\Controllers\API\V2\ReportsController@store
                        $router->get('reports', 'ReportsController@index');
                    });
                });

                // route => uri: admin/api/info, action: App\Controllers\API\MasterController@info
                $router->get('info', 'MasterController@info');
            });
        });
    });
});

return $router;
