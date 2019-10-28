<?php

namespace Tests\Dispatcher;

use Mockery;
use Tests\TestCase;
use Framework\Request;
use Tests\Dispatcher\Fixtures\FooController;
use Framework\Dispatcher\ControllerDispatcher;
use Framework\Dispatcher\Exceptions\ControllerActionNotFoundException;

class ControllerDispatcherTest extends TestCase
{
    protected $request;
    protected $dispatcher;
    protected $fooController;

    protected function setUp()
    {
        parent::setUp();

        $this->fooController = new FooController();
        $this->dispatcher = new ControllerDispatcher();
        $this->request = Mockery::mock(Request::class);
    }

    /** @test */
    public function it_triggers_the_call_action_controller_method()
    {
        $controllerMock = Mockery::mock(FooController::class);

        $arguments = ['foo' => 'bar'];

        $controllerMock->shouldReceive('callAction')
            ->with('index', $this->request, $arguments)
            ->once();

        $this->dispatcher->dispatch($this->request, $controllerMock, 'index', $arguments);
    }

    /** @test */
    public function it_throws_if_controller_action_method_does_not_exist()
    {
        $this->expectException(ControllerActionNotFoundException::class);

        $this->dispatcher->dispatch($this->request, $this->fooController, 'destroy');
    }

    /** @test */
    public function it_returns_the_response_from_controller()
    {
        $requestData = ['status' => 'success', 'foo' => 'bar'];

        $request = Request::create('reports', 'GET', $requestData);

        $response = $this->dispatcher->dispatch($request, $this->fooController, 'index');

        $this->assertEquals($requestData, $response->content);
    }

    /** @test */
    public function it_passes_route_arguments_to_controller_method_and_in_correct_order()
    {
        $routeArguments = ['var1' => 'foo', 'var2' => 'bar', 'var3' => 'baz'];

        $response = $this->dispatcher->dispatch($this->request, $this->fooController, 'store', $routeArguments);

        $expected = [0 => 'foo', 1 => 'bar', 2 => 'baz'];

        $this->assertEquals($expected, $response->content);
    }
}
