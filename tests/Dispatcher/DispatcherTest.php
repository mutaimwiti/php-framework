<?php

namespace Tests\Dispatcher;

use Mockery;
use Tests\TestCase;
use Framework\Request;
use Framework\Response;
use Framework\Controller;
use Framework\Routing\Router;
use Framework\Routing\RouteAction;
use Framework\Dispatcher\Dispatcher;
use Framework\Dispatcher\ControllerDispatcher;
use Framework\Routing\Exceptions\RouteNotFoundException;

class DispatcherTest extends TestCase
{
    protected function createRouterMock()
    {
        return Mockery::mock(Router::class);
    }

    protected function createRouteActionMock($handler, $arguments = [])
    {
        return Mockery::mock(RouteAction::class, [$handler, $arguments]);
    }

    protected function createControllerDispatcherMock()
    {
        return Mockery::mock(ControllerDispatcher::class);
    }

    /** @test */
    function it_rethrows_exceptions_from_router()
    {
        // since router should be concerned about its own exceptions
        // we only test one exception - RouteNotFoundException
        $this->expectException(RouteNotFoundException::class);

        $routerMock = $this->createRouterMock();

        $routerMock->shouldReceive('match')
            ->andThrow(RouteNotFoundException::class);

        $dispatcher = new Dispatcher($routerMock, $this->createControllerDispatcherMock());

        $dispatcher->handle(Request::create());
    }

    /** @test */
    function it_dispatches_closure_handlers_correctly()
    {
        $closure = function ($request) {
            $status = $request->get('status');

            return response("$status foo");
        };

        $routerMock = $this->createRouterMock();

        $routeActionMock = $this->createRouteActionMock($closure);

        $routerMock->shouldReceive('match')
            ->andReturn($routeActionMock);

        $request = Request::create('foo', 'GET', ['status' => 'cool']);

        $dispatcher = new Dispatcher($routerMock, $this->createControllerDispatcherMock());

        $response = $dispatcher->handle($request);

        $this->assertEquals('cool foo', $response->content);
    }

    /** @test */
    function it_dispatches_controller_handlers_correctly()
    {
        $request = Request::create();

        $controllerDispatcherMock = $this->createControllerDispatcherMock();

        $controllerDispatcherMock->shouldReceive('dispatch')
            ->with($request, Controller::class, 'index', ['john' => 'doe'])
            ->once();

        $routerMock = $this->createRouterMock();

        $routeActionMock = $this->createRouteActionMock(
            'Tests\Dispatcher\Fixtures\FooController@index',
            ['john' => 'doe']
        );

        $routerMock->shouldReceive('match')
            ->andReturn($routeActionMock);

        $dispatcher = new Dispatcher($routerMock, $controllerDispatcherMock);

        $dispatcher->handle($request);
    }

    /** @test */
    function it_automatically_casts_closure_response_to_response_instance()
    {
        $routerMock = $this->createRouterMock();

        $closure = function () {
            return [];
        };

        $routeActionMock = Mockery::mock(RouteAction::class, [$closure]);

        $routerMock->shouldReceive('match')
            ->andReturn($routeActionMock);

        $request = Request::create('foo');

        $dispatcher = new Dispatcher($routerMock, $this->createControllerDispatcherMock());

        $response = $dispatcher->handle($request);

        $this->assertEquals([], $response->content);
        $this->assertInstanceOf(Response::class, $response);
    }

    /** @test */
    function it_automatically_casts_controller_response_to_response_instance()
    {
        $routerMock = $this->createRouterMock();

        $routeActionMock = $this->createRouteActionMock('Tests\Dispatcher\Fixtures\BarController@get');

        $routerMock->shouldReceive('match')
            ->andReturn($routeActionMock);

        $request = Request::create('bar');

        $controllerDispatcherMock = $this->createControllerDispatcherMock();

        $controllerDispatcherMock->shouldReceive('dispatch')
            ->andReturn([]);

        $dispatcher = new Dispatcher($routerMock, $controllerDispatcherMock);

        $response = $dispatcher->handle($request);

        $this->assertEquals([], $response->content);
        $this->assertInstanceOf(Response::class, $response);
    }

    /** @test */
    public function it_avails_route_arguments_to_closure_and_in_correct_order()
    {
        $routerMock = $this->createRouterMock();

        $closure = function (Request $request, $var1, $var2, $var3) {
            return [$var1, $var2, $var3];
        };

        $routeArguments = ['var1' => 'foo', 'var2' => 'bar', 'var3' => 'baz'];

        $routeActionMock = Mockery::mock(RouteAction::class, [
            $closure,
            $routeArguments
        ]);

        $routerMock->shouldReceive('match')
            ->andReturn($routeActionMock);

        $dispatcher = new Dispatcher($routerMock, $this->createControllerDispatcherMock());

        $response = $dispatcher->handle(Request::create());

        $expected = [0 => 'foo', 1 => 'bar', 2 => 'baz'];

        $this->assertEquals($expected, $response->content);
    }
}
