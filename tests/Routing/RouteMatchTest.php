<?php

namespace Tests\Routing;

use Tests\TestCase;
use Framework\Request;
use Framework\Routing\Router;
use Framework\Routing\RouteAction;
use Framework\Routing\Exceptions\HTTPMethodException;
use Framework\Routing\Exceptions\RouteNotFoundException;

class RouteMatchTest extends TestCase
{
    /** @test */
    function it_returns_a_route_instance()
    {
        $router = new Router();

        $router->get('foo', 'FooController@index');

        $request = Request::create('foo', 'GET');

        $this->assertInstanceOf(RouteAction::class, $router->match($request));
    }


    /** @test */
    function it_matches_controller_requests_correctly()
    {
        $router = new Router();

        $actionString = 'UsersController@store';

        $router->get('users', $actionString);

        $request = Request::create('users', 'GET');

        $expected = new RouteAction($actionString);

        $this->assertEquals($expected, $router->match($request));
    }

    /** @test */
    function it_matches_closure_requests_correctly()
    {
        $router = new Router();

        $reportsClosure = function () {
            return 'Created Report';
        };

        $router->post('reports', $reportsClosure);

        $request = Request::create('reports', 'POST');

        $expected = new RouteAction($reportsClosure);

        $this->assertEquals($expected, $router->match($request));
    }

    /** @test */
    function it_matches_root_route_requests_correctly()
    {
        $router = new Router();

        $router->get('/', 'HomeController@index');

        $request = Request::create('/', 'GET');

        $expected = new RouteAction('HomeController@index');

        $this->assertEquals($expected, $router->match($request));
    }

    /** @test */
    function it_throws_when_invalid_invalid_http_method_is_detected()
    {
        $this->expectException(HTTPMethodException::class);

        $router = new Router();

        // simulate HEAD request
        $request = Request::create('/', 'HEAD');

        $router->match($request);
    }

    /** @test */
    function it_throws_when_route_is_not_found()
    {
        $this->expectException(RouteNotFoundException::class);

        $router = new Router();

        // simulate request
        $request = Request::create('foo', 'POST');

        $router->match($request);
    }
}
