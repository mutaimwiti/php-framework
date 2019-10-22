<?php

namespace Tests\Router;

use Tests\TestCase;
use Framework\Router\Router;

class RouteRegisterTest extends TestCase
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

        $this->assertEquals($expected, $router->routes['GET']);
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

        $this->assertEquals($expected, $router->routes['POST']);
    }

    /** @test */
    function it_correctly_registers_root_routes()
    {
        $router = new Router();

        $router->get('/', 'HomeController@index');
        $router->post('/', 'HomeController@store');

        $expected = [
            'GET' => ['/' => 'HomeController@index'],
            'POST' => ['/' => 'HomeController@store'],
        ];

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_correctly_registers_routes_with_trailing_slashes()
    {
        $router = new Router();

        $router->get('api/users/', 'UsersController@index');
        $router->post('api/users/', 'UsersController@store');

        $expected = [
            'GET' => ['api/users' => 'UsersController@index'],
            'POST' => ['api/users' => 'UsersController@store'],
        ];

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_correctly_registers_routes_with_preceding_slashes()
    {
        $router = new Router();

        $router->get('/api/users', 'UsersController@index');
        $router->post('/api/users', 'UsersController@store');

        $expected = [
            'GET' => ['api/users' => 'UsersController@index'],
            'POST' => ['api/users' => 'UsersController@store'],
        ];

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_correctly_registers_routes_with_both_preceding_and_trailing_slashes()
    {
        $router = new Router();

        $router->get('/api/users/', 'UsersController@index');
        $router->post('/api/users/', 'UsersController@store');

        $expected = [
            'GET' => ['api/users' => 'UsersController@index'],
            'POST' => ['api/users' => 'UsersController@store'],
        ];

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_correctly_registers_routes_with_multiple_preceding_or_trailing_slashes()
    {
        $router = new Router();

        $router->get('///api/users///', 'UsersController@index');
        $router->post('///api/users///', 'UsersController@store');

        $expected = [
            'GET' => ['api/users' => 'UsersController@index'],
            'POST' => ['api/users' => 'UsersController@store'],
        ];

        $this->assertEquals($expected, $router->routes);
    }
}
