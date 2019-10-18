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

        $routerMock = Mockery::mock(Router::class);

        $routerMock->shouldReceive('match')
            ->andThrow(RouteNotFoundException::class);

        $controllerDispatcherMock = Mockery::mock(ControllerDispatcher::class);

        $dispatcher = new Dispatcher($routerMock, $controllerDispatcherMock);

        $dispatcher->handle(Request::create());
    }

    /** @test */
    function it_dispatches_closure_handlers_correctly()
    {
        $closure = function ($request) {
            $status = $request->get('status');

            return "$status foo";
        };

        $routerMock = Mockery::mock(Router::class);

        $routerMock->shouldReceive('match')
            ->andReturn($closure);

        $request = Request::create('foo', 'GET', ['status' => 'cool']);

        $controllerDispatcherMock = Mockery::mock(ControllerDispatcher::class);

        $dispatcher = new Dispatcher($routerMock, $controllerDispatcherMock);

        $result = $dispatcher->handle($request);

        $this->assertEquals('cool foo', $result);
    }

    /** @test */
    function it_dispatches_controller_handlers_correctly()
    {
        $request = Request::create();

        $controllerDispatcherMock = Mockery::mock(ControllerDispatcher::class);

        $controllerDispatcherMock->shouldReceive('dispatch')
            ->with($request, Controller::class, 'index')
            ->once();

        $routerMock = Mockery::mock(Router::class);

        $routerMock->shouldReceive('match')
            ->andReturn('Tests\Dispatcher\Fixtures\FooController@index');

        $dispatcher = new Dispatcher($routerMock, $controllerDispatcherMock);

        $dispatcher->handle($request);
    }
}
