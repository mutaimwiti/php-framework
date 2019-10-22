<?php

namespace Tests\Router;

use Tests\TestCase;
use Acme\Router\Router;

class RoutePrefixTest extends TestCase {
    /** @test */
    function it_should_apply_route_prefixes()
    {
        $router = new Router();

        $reportsCallback = function () {
            return 'Reports';
        };

        $router->prefix('api', function ($router) {
            $router->get('users', 'UsersController@index');
        });

        $router->prefix('admin', function ($router) use ($reportsCallback) {
            $router->post('reports', $reportsCallback);
        });

        $expected = [
            'GET' => ['api/users' => 'UsersController@index'],
            'POST' => ['admin/reports' => $reportsCallback],
        ];

        $this->assertEquals($expected, $router->getRoutes());
    }

    /** @test */
    function it_should_apply_route_prefixes_only_to_wrapped_routes()
    {
        $router = new Router();

        $reportsCallback = function () {
            return 'Reports';
        };

        $router->prefix('api', function ($router) {
            $router->get('users', 'UsersController@index');
        });

        $router->post('reports', $reportsCallback);

        $expected = [
            'GET' => ['api/users' => 'UsersController@index'],
            'POST' => ['reports' => $reportsCallback],
        ];

        $this->assertEquals($expected, $router->getRoutes());
    }
}
