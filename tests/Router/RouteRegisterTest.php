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
    function it_registers_put_routes()
    {
        $router = new Router();

        $reportsClosure = function () {
            return 'Updated Report';
        };

        $router->put('users', 'UsersController@update');
        $router->put('reports', $reportsClosure);

        $expected = [
            'users' => 'UsersController@update',
            'reports' => $reportsClosure,
        ];

        $this->assertEquals($expected, $router->routes['PUT']);
    }

    /** @test */
    function it_registers_patch_routes()
    {
        $router = new Router();

        $reportsClosure = function () {
            return 'Updated Report';
        };

        $router->patch('users', 'UsersController@update');
        $router->patch('reports', $reportsClosure);

        $expected = [
            'users' => 'UsersController@update',
            'reports' => $reportsClosure,
        ];

        $this->assertEquals($expected, $router->routes['PATCH']);
    }

    /** @test */
    function it_registers_delete_routes()
    {
        $router = new Router();

        $reportsClosure = function () {
            return 'Deleted Report';
        };

        $router->delete('users', 'UsersController@destory');
        $router->delete('reports', $reportsClosure);

        $expected = [
            'users' => 'UsersController@destory',
            'reports' => $reportsClosure,
        ];

        $this->assertEquals($expected, $router->routes['DELETE']);
    }

    /** @test */
    function it_correctly_registers_root_routes()
    {
        $router = new Router();

        $router->get('/', 'HomeController@index');
        $router->post('/', 'HomeController@store');
        $router->put('/', 'HomeController@update');
        $router->patch('/', 'HomeController@update');
        $router->delete('/', 'HomeController@destory');

        $expected = [
            'GET' => ['/' => 'HomeController@index'],
            'POST' => ['/' => 'HomeController@store'],
            'PUT' => ['/' => 'HomeController@update'],
            'PATCH' => ['/' => 'HomeController@update'],
            'DELETE' => ['/' => 'HomeController@destory'],
        ];

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_correctly_registers_routes_with_trailing_slashes()
    {
        $router = new Router();

        $router->get('api/users/', 'UsersController@index');
        $router->post('api/users/', 'UsersController@store');
        $router->put('api/users/', 'UsersController@update');
        $router->patch('api/users/', 'UsersController@update');
        $router->delete('api/users/', 'UsersController@destory');

        $expected = [
            'GET' => ['api/users' => 'UsersController@index'],
            'POST' => ['api/users' => 'UsersController@store'],
            'PUT' => ['api/users' => 'UsersController@update'],
            'PATCH' => ['api/users' => 'UsersController@update'],
            'DELETE' => ['api/users' => 'UsersController@destory'],
        ];

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_correctly_registers_routes_with_preceding_slashes()
    {
        $router = new Router();

        $router->get('/api/users', 'UsersController@index');
        $router->post('/api/users', 'UsersController@store');
        $router->put('/api/users', 'UsersController@update');
        $router->patch('/api/users', 'UsersController@update');
        $router->delete('/api/users', 'UsersController@destory');

        $expected = [
            'GET' => ['api/users' => 'UsersController@index'],
            'POST' => ['api/users' => 'UsersController@store'],
            'PUT' => ['api/users' => 'UsersController@update'],
            'PATCH' => ['api/users' => 'UsersController@update'],
            'DELETE' => ['api/users' => 'UsersController@destory'],
        ];

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_correctly_registers_routes_with_both_preceding_and_trailing_slashes()
    {
        $router = new Router();

        $router->get('/api/users/', 'UsersController@index');
        $router->post('/api/users/', 'UsersController@store');
        $router->put('/api/users/', 'UsersController@update');
        $router->patch('/api/users/', 'UsersController@update');
        $router->delete('/api/users/', 'UsersController@destory');

        $expected = [
            'GET' => ['api/users' => 'UsersController@index'],
            'POST' => ['api/users' => 'UsersController@store'],
            'PUT' => ['api/users' => 'UsersController@update'],
            'PATCH' => ['api/users' => 'UsersController@update'],
            'DELETE' => ['api/users' => 'UsersController@destory'],
        ];

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_correctly_registers_routes_with_multiple_preceding_or_trailing_slashes()
    {
        $router = new Router();

        $router->get('///api/users///', 'UsersController@index');
        $router->post('///api/users///', 'UsersController@store');
        $router->put('///api/users///', 'UsersController@update');
        $router->patch('///api/users///', 'UsersController@update');
        $router->delete('///api/users///', 'UsersController@destory');

        $expected = [
            'GET' => ['api/users' => 'UsersController@index'],
            'POST' => ['api/users' => 'UsersController@store'],
            'PUT' => ['api/users' => 'UsersController@update'],
            'PATCH' => ['api/users' => 'UsersController@update'],
            'DELETE' => ['api/users' => 'UsersController@destory'],
        ];

        $this->assertEquals($expected, $router->routes);
    }
}
