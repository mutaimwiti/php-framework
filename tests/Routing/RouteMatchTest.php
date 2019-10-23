<?php

namespace Tests\Routing;

use Tests\TestCase;
use Framework\Request;
use Framework\Routing\Router;
use Framework\Routing\Exceptions\HTTPMethodException;
use Framework\Routing\Exceptions\RouteNotFoundException;

class RouteMatchTest extends TestCase {
    /** @test */
    function it_matches_controller_requests_correctly()
    {
        $router = new Router();

        $actionString = 'UsersController@store';

        $router->get('users', $actionString);

        // simulate request
        $request = Request::create('users', 'GET');

        $this->assertEquals($actionString, $router->match($request));
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
        $request = Request::create('reports', 'POST');
        $this->assertEquals($reportsClosure, $router->match($request));
    }

    /** @test */
    function it_matches_root_route_requests_correctly()
    {
        $router = new Router();

        $router->get('/', 'HomeController@index');
        $router->post('/', 'HomeController@store');

        // simulate GET request
        $getRequest = Request::create('/', 'GET');

        // simulate POST request
        $postRequest = Request::create('/', 'POST');

        $this->assertEquals('HomeController@index', $router->match($getRequest));
        $this->assertEquals('HomeController@store', $router->match($postRequest));
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
