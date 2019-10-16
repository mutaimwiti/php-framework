<?php

namespace Tests\Router;

use Core\Router\Router;

class RouteNamespaceTest {
    /** @test */
    function it_should_apply_controller_namespaces()
    {
        $router = new Router();

        $router->namespace('App\Controllers', function ($router) {
            $router->get('users', 'UsersController@index');
        });

        $router->namespace('App\Controllers\Core', function ($router) {
            $router->post('reports', 'ReportsController@store');
        });


        $expected = [
            'GET' => ['users' => 'App\Controllers\UsersController@index'],
            'POST' => ['reports' => 'App\Controllers\Core\ReportsController@store'],
        ];

        $this->assertEquals($expected, $router->getRoutes());
    }

    /** @test */
    function it_should_only_apply_namespaces_to_wrapped_routes()
    {
        $router = new Router();

        $router->namespace('App\Controllers', function ($router) {
            $router->post('users', 'UsersController@store');
        });

        $router->get('articles', 'ArticleController@index');

        $expected = [
            'POST' => ['users' => 'App\Controllers\UsersController@store'],
            'GET' => ['articles' => 'ArticleController@index'],
        ];

        $this->assertEquals($expected, $router->getRoutes());
    }
}
