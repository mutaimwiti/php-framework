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

    protected $_GET;
    protected $_POST;
    protected $_SERVER;

    protected function setUp()
    {
        parent::setUp();

        $this->_GET = $_GET;
        $this->_POST = $_POST;
        $this->_SERVER = $_SERVER;
    }

    protected function tearDown()
    {
        parent::tearDown();

        $_GET = $this->_GET;
        $_POST = $this->_POST;
        $_SERVER = $this->_SERVER;
    }

    /** @test */
    function it_rethrows_exceptions_from_router()
    {
        // since router should be concerned about its own exceptions
        // we only test one exception - RouteNotFoundException

        $this->expectException(RouteNotFoundException::class);

        $dispatcher = new Dispatcher(new Router(), new ControllerDispatcher());

        // simulate missing route request
        $_SERVER['REQUEST_URI'] = 'foo';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $dispatcher->handle(new Request());
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
        $_SERVER['REQUEST_URI'] = 'foo';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['status' => 'cool'];

        $result = $dispatcher->handle(new Request());

        $this->assertEquals('cool foo', $result);
    }

    /** @test */
    function it_dispatches_controller_handlers_correctly()
    {
        // simulate request
        $_SERVER['REQUEST_URI'] = 'foo';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $request = new Request();

        $controllerDispatcherMock = Mockery::mock(ControllerDispatcher::class);

        $controllerDispatcherMock->shouldReceive('dispatch')
            ->with($request, Controller::class, 'index')
            ->once();

        $router = new Router();

        $router->get('foo', 'FooController@index');

        $dispatcher = new Dispatcher($router, $controllerDispatcherMock);

        $dispatcher->setControllerNameSpace('Tests\Dispatcher\Fixtures');

        $dispatcher->handle($request);
    }
}
