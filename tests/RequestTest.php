<?php

namespace Tests;

use Exception;
use Core\Request;

class RequestTest extends TestCase
{
    protected $_GET;
    protected $_POST;
    protected $_SERVER;
    protected $_REQUEST;

    protected function setUp()
    {
        parent::setUp();

        $this->_GET = $_GET;
        $this->_POST = $_POST;
        $this->_SERVER = $_SERVER;
        $this->_REQUEST = $_REQUEST;
    }

    protected function tearDown()
    {
        parent::tearDown();

        $_GET = $this->_GET;
        $_POST = $this->_POST;
        $_SERVER = $this->_SERVER;
        $_REQUEST = $this->_REQUEST;
    }

    /** @test */
    function it_extracts_all_get_params()
    {
        $expected = $_GET = [
            "foo" => "foo_val",
            "bar" => "bar_val",
        ];

        $instance = new Request();

        $this->assertEquals($expected, ($instance->all()));
    }

    /** @test */
    function it_extracts_all_post_params()
    {
        $expected = $_POST = [
            "foo" => "foo_val",
            "bar" => "bar_val",
        ];

        $instance = new Request();

        $this->assertEquals($expected, ($instance->all()));
    }

    /** @test */
    function it_gets_a_single_key()
    {
        $_POST = [
            "foo" => "foo_val",
            "bar" => "bar_val",
        ];

        $instance = new Request();

        $this->assertEquals('foo_val', ($instance->get('foo')));
    }

    /** @test */
    function it_throws_when_key_is_missing()
    {
        $this->expectException(Exception::class);

        $instance = new Request();

        $this->assertEquals('foo_val', ($instance->get('foo')));
    }

    /** @test */
    function it_gets_correct_request_method()
    {
        $_REQUEST['REQUEST_METHOD'] = 'GET';

        $instance = new Request();

        $this->assertEquals('GET', ($instance->method()));

        $_REQUEST['REQUEST_METHOD'] = 'POST';

        $instance = new Request();

        $this->assertEquals('POST', ($instance->method()));
    }

    /** @test */
    function it_gets_the_correct_uri()
    {
        $_SERVER['REQUEST_URI'] = '/foo/bar/?php=true';

        $instance = new Request();

        $this->assertEquals('foo/bar', ($instance->uri()));
    }
}
