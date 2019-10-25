<?php

namespace Tests\Routing;

use Tests\TestCase;
use Framework\Routing\RouteAction;
use Framework\Routing\RouteMatcher;

class RouteMatcherTest extends TestCase
{
    protected $routes = [
        // simple routes
        '/' => 'HomeController@index',
        'users/stats' => 'UserController@doSomething',
        'reports/system' => 'ReportController@doSomething',
        // parameterized routes
        'bar/{id?}/{name?}' => 'BarController@doSomething',
        'foo/{id}/bar/{name?}' => 'FooController@doSomething',
        'baz/{id}/{name?}/{col?}/' => 'BazController@doSomething',
        // potentially crashing routes
        // case 1
        'stats/foo' => 'StatsController@doX',
        'stats/{prop}' => 'StatsController@doY',
        // case 2
        'articles/{id}' => 'ArticleController@show',
        'articles/meta' => 'ArticleController@meta',
    ];

    /** @var RouteMatcher */
    protected $routeMatcher;

    protected function setUp()
    {
        parent::setUp();

        $this->routeMatcher = new RouteMatcher($this->routes);
    }

    /** @test */
    function it_returns_a_route_action_instance_on_match()
    {
        // route => bar/{id?}/{name?}
        $this->assertInstanceOf(RouteAction::class, $this->routeMatcher->match('bar'));
    }

    /** @test */
    function it_returns_false_on_match_failure()
    {
        // undefined route => something
        $this->assertEquals(false, $this->routeMatcher->match('something'));
    }

    /** @test */
    function it_passes_through_the_action_and_arguments()
    {
        // route => bar/{id?}/{name?}
        $expected = new RouteAction('BarController@doSomething', ['23', '85']);

        $this->assertEquals($expected, $this->routeMatcher->match('bar/23/85'));
    }

    /** @test */
    function it_passes_through_the_action_and_an_empty_argument_list_for_simple_routes()
    {
        // route => users/stats
        $expected = new RouteAction('UserController@doSomething', []);

        $this->assertEquals($expected, $this->routeMatcher->match('users/stats'));
    }

    /** @test */
    function missing_arguments_are_set_to_null() {
        // route => bar/{id?}/{name?}
        $expected = new RouteAction('BarController@doSomething', [null, null]);

        $this->assertEquals($expected, $this->routeMatcher->match('bar'));
    }

    /** @test */
    function it_matches_optional_arguments_correctly()
    {
        // route => bar/{id?}/{name?}
        $expected = new RouteAction('BarController@doSomething', [null, null]);
        $this->assertEquals($expected, $this->routeMatcher->match('bar'));

        // route => foo/{id}/bar/{name?}
        $expected = new RouteAction('FooController@doSomething', [5, null]);
        $this->assertEquals($expected, $this->routeMatcher->match('foo/5/bar'));

        // route => baz/{id}/{name?}/{col?}/
        $expected = new RouteAction('BazController@doSomething', [55, null, null]);
        $this->assertEquals($expected, $this->routeMatcher->match('baz/55'));
    }

    /** @test */
    function trailing_slashes_after_optional_arguments_are_optional()
    {
        // route => baz/{id}/{name?}/{col?}/
        $expected = new RouteAction('BazController@doSomething', [44, null, null]);

        $this->assertEquals($expected, $this->routeMatcher->match('baz/44'));
        $this->assertEquals($expected, $this->routeMatcher->match('baz/44/'));
    }

    /** @test */
    function it_matches_root_route_requests_correctly()
    {
        // route => /
        $expected = new RouteAction('HomeController@index');

        $this->assertEquals($expected, $this->routeMatcher->match('/'));
    }

    /** @test */
    function order_of_route_def_is_respected_when_simple_routes_crash_with_parameterized_routes() {
        // case 1
        // route => stats/foo
        $expected = new RouteAction('StatsController@doX');
        $this->assertEquals($expected, $this->routeMatcher->match('stats/foo'));

        // route => stats/{prop}
        $expected = new RouteAction('StatsController@doY', ['speed']);
        $this->assertEquals($expected, $this->routeMatcher->match('stats/speed'));

        // case 2
        // route => articles/{id}
        $expected = new RouteAction('ArticleController@show', ['7']);
        $this->assertEquals($expected, $this->routeMatcher->match('articles/7'));

        // route => articles/meta - will match articles/{id} instead
        $expected = new RouteAction('ArticleController@show', ['meta']);
        $this->assertEquals($expected, $this->routeMatcher->match('articles/meta'));
    }
}
