<?php

namespace Tests\Router;

use Core\Request;
use Tests\TestCase;
use Core\Router\Router;
use Core\Router\Exceptions\HTTPMethodException;
use Core\Router\Exceptions\RouteNotFoundException;

class RouteMatchTest extends TestCase {
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
}
