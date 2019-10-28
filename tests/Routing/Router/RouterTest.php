<?php

namespace Tests\Routing;

use Tests\TestCase;
use Framework\Routing\Router;
use Tests\Utilities\Routing\RoutesBare;

class RouterTest extends TestCase
{
    use RoutesBare;

    /** @test */
    function it_returns_all_routes()
    {
        $router = new Router();

        $expected = $this->routesBare;

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_returns_null_on_attempt_to_get_missing_property()
    {
        $router = new Router();

        $this->assertEquals(null, $router->something);
    }
}
