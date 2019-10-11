<?php

namespace Tests;

use Core\Request;
use Core\Router\Router;
use Core\Router\HTTPMethodException;
use Core\Router\RouteNotFoundException;

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
    function it_matches_controller_requests_correctly() {
        $router = new Router();

        $actionString = 'UsersController@store';

        $router->get('users', $actionString);

        // simulate request
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = 'users';

        $this->assertEquals($actionString, $router->match(new Request()));
    }

    /** @test */
    function it_matches_closure_requests_correctly() {
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
    function it_throws_when_invalid_invalid_http_method_is_detected() {
        $this->expectException(HTTPMethodException::class);

        $router = new Router();

        // simulate request
        $_SERVER['REQUEST_METHOD'] = 'HEAD';

        $router->match(new Request());
    }

    /** @test */
    function it_throws_when_route_is_not_found() {
        $this->expectException(RouteNotFoundException::class);

        $router = new Router();

        // simulate request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = 'foo';

        $router->match(new Request());
    }
}
