<?php

namespace Tests\Routing;

use Tests\TestCase;
use Framework\Routing\Router;
use Tests\Utilities\Routing\RoutesBare;

class RouteGroupTest extends TestCase
{
    use RoutesBare;

    /** @test */
    function it_should_apply_route_groups_correctly()
    {
        // get routes with complex nesting of namespaces and prefixes
        $router = new Router();

        $usersCallback = function () {
            return 'Users';
        };

        $router->group(['namespace' => 'Controllers', 'prefix' => 'api'], function (Router $router) use ($usersCallback) {
            $router->post('reports', 'ReportController@store');
            $router->get('users', $usersCallback);
        });

        $expected = array_merge($this->routesBare, [
            'POST' => ['api/reports' => 'Controllers\ReportController@store'],
            'GET' => ['api/users' => $usersCallback],
        ]);

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_should_apply_route_groups_only_to_wrapped_routes()
    {
        $router = new Router();

        $router->group(['namespace' => 'App\Controllers', 'prefix' => 'api'], function ($router) {
            $router->patch('reports', 'ReportController@update');
        });

        $router->put('users', 'UserController@update');

        $expected = array_merge($this->routesBare, [
            'PATCH' => ['api/reports' => 'App\Controllers\ReportController@update'],
            'PUT' => ['users' => 'UserController@update'],
        ]);

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_should_allow_creation_of_route_groups_with_prefix_only_namespace_only_or_no_options()
    {
        $router = new Router();

        // zero options - no effect
        $router->group([], function ($router) {
            $router->post('reports', 'ReportController@store');
        });

        // namespace only
        $router->group(['namespace' => 'App\Controllers'], function ($router) {
            $router->patch('users', 'UserController@update');
        });

        // prefix only
        $router->group(['prefix' => 'api'], function ($router) {
            $router->delete('articles', 'ArticleController@destroy');
        });

        $expected = array_merge($this->routesBare, [
            'POST' => ['reports' => 'ReportController@store'],
            'PATCH' => ['users' => 'App\Controllers\UserController@update'],
            'DELETE' => ['api/articles' => 'ArticleController@destroy']
        ]);

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_should_ignore_invalid_group_options()
    {
        $router = new Router();

        // zero options - no effect
        $router->group(['foo' => 'bar'], function ($router) {
            $router->get('users', 'UserController@index');
            $router->post('reports', 'ReportController@store');
        });

        $expected = array_merge($this->routesBare, [
            'GET' => ['users' => 'UserController@index'],
            'POST' => ['reports' => 'ReportController@store']
        ]);

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_should_apply_namespace_prefix_and_group_nests()
    {
        // get routes with complex nesting of namespaces and prefixes
        $router = require 'fixtures/nested_routes.php';

        $expected = array_merge($this->routesBare, [
            'POST' => [
                'admin/api/v1/users' => 'App\Controllers\API\V1\UsersController@store'
            ],
            'GET' => [
                'admin/api/v2/reports' => 'App\Controllers\API\V2\ReportsController@index',
                'admin/api/info' => 'App\Controllers\API\MasterController@info',
            ],
        ]);

        $this->assertEquals($expected, $router->routes);
    }
}
