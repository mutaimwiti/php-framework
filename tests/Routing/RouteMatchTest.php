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
    function it_returns_a_route_action_instance()
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
    function it_matches_parameterized_route_requests_correctly()
    {
        $router = new Router();

        $router->get('users/{id}/meta/{prop}', 'UserController@meta');

        $request = Request::create('users/723/meta/age', 'GET');

        $expected = new RouteAction('UserController@meta', ['723', 'age']);

        $this->assertEquals($expected, $router->match($request));
    }

    /** @test */
    function it_matches_optional_parameterized_route_requests_correctly()
    {
        $router = new Router();

        $router->get('reports/{year}/system/{id?}', 'ReportsController@system');

        $request = Request::create('reports/2019/system', 'GET');

        $expected = new RouteAction('ReportsController@system', ['2019', null]);

        $this->assertEquals($expected, $router->match($request));
    }

    /** @test */
    function it_throws_on_attempt_to_match_invalid_http_method()
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
