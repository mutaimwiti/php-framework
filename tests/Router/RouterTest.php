<?php

namespace Tests\Router;

use Framework\Router\Router;
use Tests\TestCase;

class RouterTest extends TestCase
{
    /** @test */
    function it_returns_all_routes()
    {
        $router = new Router();

        $expected = ['GET' => [], 'POST' => []];

        $this->assertEquals($expected, $router->routes);
    }

    /** @test */
    function it_returns_null_on_attempt_to_get_missing_property()
    {
        $router = new Router();

        $this->assertEquals(null, $router->something);
    }
}
