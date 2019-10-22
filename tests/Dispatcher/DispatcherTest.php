<?php

namespace Tests\Dispatcher;

use Mockery;
use Framework\Request;
use Framework\Response;
use Tests\TestCase;
use Framework\Controller;
use Framework\Router\Router;
use Framework\Dispatcher\Dispatcher;
use Framework\Dispatcher\ControllerDispatcher;
use Framework\Router\Exceptions\RouteNotFoundException;

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

            return response("$status foo");
        };

        $routerMock = Mockery::mock(Router::class);

        $routerMock->shouldReceive('match')
            ->andReturn($closure);

        $request = Request::create('foo', 'GET', ['status' => 'cool']);

        $controllerDispatcherMock = Mockery::mock(ControllerDispatcher::class);

        $dispatcher = new Dispatcher($routerMock, $controllerDispatcherMock);

        $response = $dispatcher->handle($request);

        $this->assertEquals('cool foo', $response->content);
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

    /** @test */
    function it_automatically_casts_closure_response_to_response_instance()
    {
        $routerMock = Mockery::mock(Router::class);

        $routerMock->shouldReceive('match')
            ->andReturn(function () {
                return [];
            });

        $request = Request::create('foo');

        $controllerDispatcherMock = Mockery::mock(ControllerDispatcher::class);

        $dispatcher = new Dispatcher($routerMock, $controllerDispatcherMock);

        $response = $dispatcher->handle($request);

        $this->assertEquals([], $response->content);
        $this->assertInstanceOf(Response::class, $response);
    }

    /** @test */
    function it_automatically_casts_controller_response_to_response_instance()
    {
        $routerMock = Mockery::mock(Router::class);

        $routerMock->shouldReceive('match')
            ->andReturn('Tests\Dispatcher\Fixtures\BarController@get');

        $request = Request::create('bar');

        $controllerDispatcherMock = Mockery::mock(ControllerDispatcher::class);

        $controllerDispatcherMock->shouldReceive('dispatch')
            ->andReturn([]);

        $dispatcher = new Dispatcher($routerMock, $controllerDispatcherMock);

        $response = $dispatcher->handle($request);

        $this->assertEquals([], $response->content);
        $this->assertInstanceOf(Response::class, $response);
    }
}
