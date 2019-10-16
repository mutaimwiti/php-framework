<?php

namespace Tests\Router;

use Tests\TestCase;
use Core\Router\Router;

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
    function it_correctly_registers_root_routes()
    {
        $router = new Router();

        $router->get('/', 'HomeController@index');
        $router->post('/', 'HomeController@store');

        $expected = [
            'GET' => ['/' => 'HomeController@index'],
            'POST' => ['/' => 'HomeController@store'],
        ];

        $this->assertEquals($expected, $router->getRoutes());
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
}
