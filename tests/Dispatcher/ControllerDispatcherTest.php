<?php

namespace Tests\Dispatcher;

use Mockery;
use Core\Request;
use Tests\Dispatcher\Fixtures\FooController;
use Tests\TestCase;
use Core\Dispatcher\ControllerDispatcher;
use Core\Dispatcher\Exceptions\ControllerActionNotFoundException;

class ControllerDispatcherTest extends TestCase
{

    protected $dispatcher;
    protected $fooController;

    protected function setUp()
    {
        parent::setUp();

        $this->dispatcher = new ControllerDispatcher();
        $this->fooController = new FooController();
    }

    /** @test */
    public function it_triggers_the_call_action_controller_method()
    {
        $request = new Request();

        $controllerMock = Mockery::mock(FooController::class);

        $controllerMock->shouldReceive('callAction')
            ->with('index', $request)
            ->once();

        $this->dispatcher->dispatch($request, $controllerMock, 'index');
    }

    /** @test */
    public function it_throws_if_controller_action_method_does_not_exist()
    {
        $this->expectException(ControllerActionNotFoundException::class);

        $request = new Request();

        $this->dispatcher->dispatch($request, $this->fooController, 'store');
    }

    /** @test */
    public function it_returns_the_response_from_controller()
    {
        $request = new Request();

        $response = $this->dispatcher->dispatch($request, $this->fooController, 'index');

        $this->assertEquals($request, $response);
    }
}
