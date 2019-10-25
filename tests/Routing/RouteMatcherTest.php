<?php

namespace Tests\Routing;

use Tests\TestCase;
use Framework\Routing\RouteAction;
use Framework\Routing\RouteMatcher;

class RouteMatcherTest extends TestCase
{
    /** @test */
    function it_returns_a_route_action_instance_on_match()
    {
        $routes = ['bar' => 'Action'];

        $routeMatcher = new RouteMatcher($routes);

        $this->assertInstanceOf(RouteAction::class, $routeMatcher->match('bar'));
    }

    /** @test */
    function it_returns_false_on_match_failure()
    {
        $routeMatcher = new RouteMatcher([]);

        // undefined route => something
        $this->assertEquals(false, $routeMatcher->match('something'));
    }

    /** @test */
    function it_passes_through_the_action_and_arguments()
    {
        $routes = ['bar/{id?}/{name?}' => 'Action'];

        $routeMatcher = new RouteMatcher($routes);

        $expected = new RouteAction('Action', ['id' => '23', 'name' => '85']);

        $this->assertEquals($expected, $routeMatcher->match('bar/23/85'));
    }

    /** @test */
    function it_passes_through_the_action_and_an_empty_argument_list_for_simple_routes()
    {
        $routes = ['users/stats' => 'Action'];

        $routeMatcher = new RouteMatcher($routes);

        $expected = new RouteAction('Action', []);

        $this->assertEquals($expected, $routeMatcher->match('users/stats'));
    }

    /** @test */
    function missing_arguments_are_set_to_null()
    {
        $routes = ['bar/{id?}/{name?}' => 'Action'];

        $routeMatcher = new RouteMatcher($routes);

        $expected = new RouteAction('Action', ['id' => null, 'name' => null]);

        $this->assertEquals($expected, $routeMatcher->match('bar'));
    }

    // missing required cause error

    /** @test */
    function it_matches_optional_arguments_correctly()
    {
        $routes = [
            'bar/{id?}/{name?}' => 'BarController@doSomething',
            'foo/{id}/bar/{name?}' => 'FooController@doSomething',
            'baz/{id}/{name?}/{col?}/' => 'BazController@doSomething',
        ];

        $routeMatcher = new RouteMatcher($routes);

        $expected = new RouteAction('BarController@doSomething', ['id' => null, 'name' => null]);
        $this->assertEquals($expected, $routeMatcher->match('bar'));

        $expected = new RouteAction('FooController@doSomething', ['id' => 5, 'name' => null]);
        $this->assertEquals($expected, $routeMatcher->match('foo/5/bar'));

        $expected = new RouteAction('BazController@doSomething', ['id' => 55, 'name' => null, 'col' => null]);
        $this->assertEquals($expected, $routeMatcher->match('baz/55'));
    }

    /** @test */
    function trailing_slashes_that_follow_optional_arguments_are_optional()
    {
        $routes = ['baz/{id?}/{name?}' => 'Action'];

        $routeMatcher = new RouteMatcher($routes);

        $expected = new RouteAction('Action', ['id' => '44', 'name' => null]);

        $this->assertEquals($expected, $routeMatcher->match('baz/44'));
        $this->assertEquals($expected, $routeMatcher->match('baz/44/'));
    }

    /** @test */
    function it_matches_root_route_requests_correctly()
    {
        $routes = ['/' => 'Action'];

        $routeMatcher = new RouteMatcher($routes);

        $expected = new RouteAction('Action');

        $this->assertEquals($expected, $routeMatcher->match('/'));
    }

    /** @test */
    function order_of_route_def_is_respected_when_simple_routes_crash_with_parameterized_routes()
    {
        $routes = [
            // case 1
            'stats/foo' => 'StatsController@doX',
            'stats/{prop}' => 'StatsController@doY',
            // case 2
            'articles/{id}' => 'ArticleController@show',
            'articles/meta' => 'ArticleController@meta',
        ];

        $routeMatcher = new RouteMatcher($routes);

        // case 1
        // route => stats/foo
        $expected = new RouteAction('StatsController@doX');
        $this->assertEquals($expected, $routeMatcher->match('stats/foo'));

        // route => stats/{prop}
        $expected = new RouteAction('StatsController@doY', ['prop' => 'speed']);
        $this->assertEquals($expected, $routeMatcher->match('stats/speed'));

        // case 2
        // route => articles/{id}
        $expected = new RouteAction('ArticleController@show', ['id' => '7']);
        $this->assertEquals($expected, $routeMatcher->match('articles/7'));

        // route => articles/meta - will match articles/{id} instead
        $expected = new RouteAction('ArticleController@show', ['id' => 'meta']);
        $this->assertEquals($expected, $routeMatcher->match('articles/meta'));
    }

    /** @test */
    function it_allows_letters_numbers_and_underscores_in_route_parameter_definition()
    {
        $routes = ['foo/{bar_baz_1}' => 'Action'];

        $routeMatcher = new RouteMatcher($routes);

        $expected = new RouteAction('Action', ['bar_baz_1' => 'something']);
        $this->assertEquals($expected, $routeMatcher->match('foo/something'));
    }

    /** @test */
    function match_fails_if_parameter_definition_starts_with_a_number()
    {
        $routes = ['foo/{1_bar_baz}' => 'Action'];

        $routeMatcher = new RouteMatcher($routes);

        $this->assertEquals(false, $routeMatcher->match('foo/something'));
    }

    /** @test */
    function match_fails_if_parameter_definition_contains_special_character()
    {
        $specialChars = ["~!@#$%^&*()_+={}|[];',./?"];

        foreach ($specialChars as $char) {
            $routes = ["foo/{$char}" => 'Action'];

            $routeMatcher = new RouteMatcher($routes);

            $routeMatcher->match("foo/val");

            $this->assertEquals(false, $routeMatcher->match('foo/something'));
        }
    }

    /** @test */
    function parameter_definition_can_be_just_an_underscore_or_underscores()
    {
        $routes = [
            'foo/{_}' => 'ActionX',
            'bar/{__}' => 'ActionY',
        ];

        $routeMatcher = new RouteMatcher($routes);

        $expected = new RouteAction('ActionX', ['_' => 'something']);
        $this->assertEquals($expected, $routeMatcher->match('foo/something'));

        $expected = new RouteAction('ActionY', ['__' => 'something']);
        $this->assertEquals($expected, $routeMatcher->match('bar/something'));
    }

    /** @test */
    function it_matches_arguments_that_include_rfc_unreserved_characters()
    {
        // unreserved  = ALPHA / DIGIT / "-" / "." / "_" / "~"

        $routes = ['foo/{id}' => 'Action'];

        $routeMatcher = new RouteMatcher($routes);

        $letters = array_merge(range('a', 'z'), range('A', 'Z'));

        $digits = range(0, 9);

        $allowedChars = array_merge($letters, $digits, ['-', '.', '_', '~']);

        foreach ($allowedChars as $char) {
            $expected = new RouteAction('Action', ['id' => $char]);
            $this->assertEquals($expected, $routeMatcher->match("foo/${char}"));
        }
    }
}
