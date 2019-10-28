<?php

namespace Tests\Dispatcher;

use Mockery;
use Tests\TestCase;
use Framework\Request;
use Framework\Response;
use Framework\Controller;
use Framework\Routing\Router;
use Framework\Dispatcher\Dispatcher;
use Framework\Dispatcher\ControllerDispatcher;
use Framework\Routing\Exceptions\RouteNotFoundException;

class DispatcherTest extends TestCase
{
    protected function createRouter()
    {
        return new Router();
    }

    protected function createControllerDispatcher()
    {
        return new ControllerDispatcher();
    }

    /** @test */
    function it_rethrows_exceptions_from_router()
    {
        // since router should be concerned about its own exceptions
        // we only test one exception - RouteNotFoundException
        $this->expectException(RouteNotFoundException::class);

        $router = $this->createRouter();

        $dispatcher = new Dispatcher($router, $this->createControllerDispatcher());

        $dispatcher->handle(Request::create());
    }

    /** @test */
    function it_dispatches_closure_handlers_correctly()
    {
        $closure = function ($request) {
            $status = $request->get('status');

            return response("$status foo");
        };

        $router = $this->createRouter();
        $router->get('foo', $closure);

        $request = Request::create('foo', 'GET', ['status' => 'cool']);

        $dispatcher = new Dispatcher($router, $this->createControllerDispatcher());

        $response = $dispatcher->handle($request);

        $this->assertEquals('cool foo', $response->content);
    }

    /** @test */
    function it_dispatches_controller_actions_correctly()
    {
        $request = Request::create();

        $controllerDispatcherMock = Mockery::mock(ControllerDispatcher::class);

        $controllerDispatcherMock->shouldReceive('dispatch')
            ->with($request, Controller::class, 'index', [])
            ->once();

        $router = $this->createRouter();
        $router->get('/', 'Tests\Dispatcher\Fixtures\FooController@index');

        $dispatcher = new Dispatcher($router, $controllerDispatcherMock);
        $dispatcher->handle($request);
    }

    /** @test */
    function it_automatically_casts_closure_response_to_response_instance()
    {
        $closure = function () {
            return [];
        };

        $router = $this->createRouter();
        $router->get('foo', $closure);

        $request = Request::create('foo');

        $dispatcher = new Dispatcher($router, $this->createControllerDispatcher());

        $response = $dispatcher->handle($request);

        $this->assertEquals([], $response->content);
        $this->assertInstanceOf(Response::class, $response);
    }

    /** @test */
    function it_automatically_casts_controller_response_to_response_instance()
    {
        $router = $this->createRouter();
        $router->get('foo', 'Tests\Dispatcher\Fixtures\FooController@show');

        $request = Request::create('foo');

        $dispatcher = new Dispatcher($router, $this->createControllerDispatcher());

        $response = $dispatcher->handle($request);

        $this->assertEquals([], $response->content);
        $this->assertInstanceOf(Response::class, $response);
    }

    /** @test */
    public function it_passes_route_arguments_to_closure_and_in_correct_order()
    {
        $closure = function (Request $request, $var1, $var2, $var3) {
            return [$var1, $var2, $var3];
        };

        $router = $this->createRouter();
        $router->get('reports/{var1}/{var2}/{var3}', $closure);

        $request = Request::create('reports/foo/bar/baz');

        $dispatcher = new Dispatcher($router, $this->createControllerDispatcher());
        $response = $dispatcher->handle($request);

        $expected = [0 => 'foo', 1 => 'bar', 2 => 'baz'];

        $this->assertEquals($expected, $response->content);
    }

    /** @test */
    function it_sets_route_params_on_the_closure_injected_request_object()
    {
        $closure = function (Request $request) {
            return [$request->route('foo'), $request->route('bar')];
        };

        $router = $this->createRouter();
        $router->get('reports/{foo}/{bar}', $closure);

        $request = Request::create('reports/foo_val/bar_val');

        $dispatcher = new Dispatcher($router, $this->createControllerDispatcher());

        $response = $dispatcher->handle($request);

        $this->assertEquals(['foo_val', 'bar_val'], $response->content);
    }

    /** @test */
    function it_sets_route_params_on_the_controller_action_injected_request_object()
    {
        $router = $this->createRouter();
        $router->get('reports/{foo}/{bar}', 'Tests\Dispatcher\Fixtures\FooController@search');

        $request = Request::create('reports/foo_val/bar_val');

        $dispatcher = new Dispatcher($router, new ControllerDispatcher());

        $response = $dispatcher->handle($request);

        $this->assertEquals(['foo_val', 'bar_val'], $response->content);
    }
}
