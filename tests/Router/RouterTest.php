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

    /** @test */
    function it_should_apply_route_groups_correctly()
    {
        // get routes with complex nesting of namespaces and prefixes
        $router = new Router();

        $usersCallback = function () {
            return 'Users';
        };

        $router->group(['namespace' => 'Controllers', 'prefix' => 'api'], function ($router) use ($usersCallback) {
            $router->post('reports', 'ReportController@store');
            $router->get('users', $usersCallback);
        });

        $expected = [
            'POST' => ['api/reports' => 'Controllers\ReportController@store'],
            'GET' => ['api/users' => $usersCallback],
        ];

        $this->assertEquals($expected, $router->getRoutes());
    }

    /** @test */
    function it_should_apply_route_groups_only_to_wrapped_routes()
    {
        $router = new Router();

        $router->group(['namespace' => 'App\Controllers', 'prefix' => 'api'], function ($router) {
            $router->post('reports', 'ReportController@store');
        });

        $router->get('users', 'UserController@index');

        $expected = [
            'POST' => ['api/reports' => 'App\Controllers\ReportController@store'],
            'GET' => ['users' => 'UserController@index'],
        ];

        $this->assertEquals($expected, $router->getRoutes());
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
            $router->get('users', 'UserController@index');
        });

        // prefix only
        $router->group(['prefix' => 'api'], function ($router) {
            $router->post('articles', 'ArticleController@store');
        });

        $expected = [
            'POST' => [
                'reports' => 'ReportController@store',
                'api/articles' => 'ArticleController@store',
            ],
            'GET' => [
                'users' => 'App\Controllers\UserController@index',
            ]
        ];

        $this->assertEquals($expected, $router->getRoutes());
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

        $expected = [
            'GET' => ['users' => 'UserController@index'],
            'POST' => ['reports' => 'ReportController@store']
        ];

        $this->assertEquals($expected, $router->getRoutes());
    }

    /** @test */
    function it_should_apply_namespace_prefix_and_group_nests()
    {
        // get routes with complex nesting of namespaces and prefixes
        $router = require 'fixtures/nested_routes.php';

        $expected = [
            'POST' => [
                'admin/api/v1/users' => 'App\Controllers\API\V1\UsersController@store'
            ],
            'GET' => [
                'admin/api/v2/reports' => 'App\Controllers\API\V2\ReportsController@index',
                'admin/api/info' => 'App\Controllers\API\MasterController@info',
            ],
        ];

        $this->assertEquals($expected, $router->getRoutes());
    }
}
