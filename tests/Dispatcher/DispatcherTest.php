<?php

namespace Tests\Dispatcher;

use Mockery;
use Core\Request;
use Tests\TestCase;
use Core\Controller;
use Core\Router\Router;
use Core\Dispatcher\Dispatcher;
use Core\Dispatcher\ControllerDispatcher;
use Core\Router\Exceptions\RouteNotFoundException;

class DispatcherTest extends TestCase
{
    /** @test */
    function it_rethrows_exceptions_from_router()
    {
        // since router should be concerned about its own exceptions
        // we only test one exception - RouteNotFoundException

        $this->expectException(RouteNotFoundException::class);

        $dispatcher = new Dispatcher(new Router(), new ControllerDispatcher());

        // simulate missing route request
        $request = Request::create('foo', 'GET');

        $dispatcher->handle($request);
    }

    /** @test */
    function it_dispatches_closure_handlers_correctly()
    {
        $router = new Router();

        $router->get('foo', function ($request) {
            $status = $request->get('status');

            return "$status foo";
        });

        $dispatcher = new Dispatcher($router, new ControllerDispatcher());

        // simulate request
        $request = Request::create('foo', 'GET', ['status' => 'cool']);

        $result = $dispatcher->handle($request);

        $this->assertEquals('cool foo', $result);
    }

    /** @test */
    function it_dispatches_controller_handlers_correctly()
    {
        // simulate request
        $request = Request::create('foo', 'GET');

        $controllerDispatcherMock = Mockery::mock(ControllerDispatcher::class);

        $controllerDispatcherMock->shouldReceive('dispatch')
            ->with($request, Controller::class, 'index')
            ->once();

        $router = new Router();

        $router->namespace('Tests\Dispatcher\Fixtures', function ($router) {
            $router->get('foo', 'FooController@index');
        });

        $dispatcher = new Dispatcher($router, $controllerDispatcherMock);

        $dispatcher->handle($request);
    }
}
