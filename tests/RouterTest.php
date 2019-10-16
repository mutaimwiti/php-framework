<?php

namespace Tests;

use Core\Request;
use Core\Router\Router;
use Core\Router\Exceptions\HTTPMethodException;
use Core\Router\Exceptions\RouteNotFoundException;

class RouterTest extends TestCase
{

    /** @test */
    function it_registers_get_routes()
    {
        $router = new Router();

        $reportsClosure = function () {
            return [
                ['title' => 'Boy', 'body' => 'Girl'],
                ['title' => 'Man', 'body' => 'Woman'],
            ];
        };

        $router->get('users', 'UsersController@index');
        $router->get('reports', $reportsClosure);

        $expected = [
            'users' => 'UsersController@index',
            'reports' => $reportsClosure,
        ];

        $this->assertEquals($expected, $router->getRoutes('GET'));
    }

    /** @test */
    function it_registers_post_routes()
    {
        $router = new Router();

        $reportsClosure = function () {
            return 'Created Report';
        };

        $router->post('users', 'UsersController@store');
        $router->post('reports', $reportsClosure);

        $expected = [
            'users' => 'UsersController@store',
            'reports' => $reportsClosure,
        ];

        $this->assertEquals($expected, $router->getRoutes('POST'));
    }

    /** @test */
    function it_returns_all_routes()
    {
        $router = new Router();

        $router->get('users', 'UsersController@index');
        $router->post('users', 'UsersController@store');

        $expected = [
            'GET' => ['users' => 'UsersController@index'],
            'POST' => ['users' => 'UsersController@store'],
        ];

        $this->assertEquals($expected, $router->getRoutes());
    }

    /** @test */
    function it_matches_controller_requests_correctly()
    {
        $router = new Router();

        $actionString = 'UsersController@store';

        $router->get('users', $actionString);

        // simulate request
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = 'users';

        $this->assertEquals($actionString, $router->match(new Request()));
    }

    /** @test */
    function it_matches_closure_requests_correctly()
    {
        $router = new Router();

        $reportsClosure = function () {
            return 'Created Report';
        };

        $router->post('reports', $reportsClosure);

        // simulate request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = 'reports';

        $this->assertEquals($reportsClosure, $router->match(new Request()));
    }

    /** @test */
    function it_throws_when_invalid_invalid_http_method_is_detected()
    {
        $this->expectException(HTTPMethodException::class);

        $router = new Router();

        // simulate request
        $_SERVER['REQUEST_METHOD'] = 'HEAD';

        $router->match(new Request());
    }

    /** @test */
    function it_throws_when_route_is_not_found()
    {
        $this->expectException(RouteNotFoundException::class);

        $router = new Router();

        // simulate request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = 'foo';

        $router->match(new Request());
    }

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

    /** @test */
    function it_should_apply_route_groups()
    {
        $router = new Router();

        $reportsCallback = function () {
            return 'Reports';
        };

        $router->group('api', function ($router) {
            $router->get('users', 'UsersController@index');
        });

        $router->group('admin', function ($router) use ($reportsCallback) {
            $router->post('reports', $reportsCallback);
        });

        $expected = [
            'GET' => ['api/users' => 'UsersController@index'],
            'POST' => ['admin/reports' => $reportsCallback],
        ];

        $this->assertEquals($expected, $router->getRoutes());
    }

    /** @test */
    function it_should_only_apply_route_groups_to_wrapped_routes()
    {
        $router = new Router();

        $reportsCallback = function () {
            return 'Reports';
        };

        $router->group('api', function ($router) {
            $router->get('users', 'UsersController@index');
        });

        $router->post('reports', $reportsCallback);

        $expected = [
            'GET' => ['api/users' => 'UsersController@index'],
            'POST' => ['reports' => $reportsCallback],
        ];

        $this->assertEquals($expected, $router->getRoutes());
    }

    /** @test */
    function it_should_apply_namespace_and_route_group_nests()
    {
        $router = new Router();

        $router->namespace('Controllers\Api', function ($router) {
            $router->group('api', function ($router) {
                $router->group('v1', function ($router) {
                    $router->post('users', 'UsersController@store');
                });

                $router->get('info', 'MasterController@info');
            });
        });

        $expected = [
            'POST' => ['api/v1/users' => 'Controllers\Api\UsersController@store'],
            'GET' => ['api/info' => 'Controllers\Api\MasterController@info'],
        ];

        $this->assertEquals($expected, $router->getRoutes());
    }
}
