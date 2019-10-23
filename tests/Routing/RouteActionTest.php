<?php

namespace Tests\Routing;

use Tests\TestCase;
use Framework\Routing\RouteAction;

class RouteActionTest extends TestCase {
    /** @test */
    function it_allows_access_of_handler_and_arguments() {
        $handler = 'FooController@index';
        $arguments = ['foo', 'bar'];

        $routeAction = new RouteAction($handler, $arguments);

        $this->assertEquals($handler, $routeAction->handler);
        $this->assertEquals($arguments, $routeAction->arguments);
    }

    /** @test */
    function it_defaults_to_empty_array_for_arguments() {
        $routeAction = new RouteAction('FooController@index');

        $this->assertEquals([], $routeAction->arguments);
    }

    /** @test */
    function it_returns_null_on_access_of_non_exitent_properties() {
        $routeAction = new RouteAction('FooController@index');

        $this->assertEquals(null, $routeAction->somthing);
    }
}
