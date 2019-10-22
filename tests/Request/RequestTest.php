<?php

namespace Tests\Request;

use Tests\TestCase;
use Framework\Request;

class RequestTest extends TestCase
{
    protected $rawInputFixtureFile;
    protected $phpRawInputStream = 'php://input';

    protected function setUp()
    {
        parent::setUp();

        $this->rawInputFixtureFile = __DIR__ . '/fixtures/raw_input.json';
    }

    /** @test */
    function it_extracts_all_get_params()
    {
        $expected = $get = [
            "foo" => "foo_val",
            "bar" => "bar_val",
        ];

        $request = new Request($get, [], []);

        $this->assertEquals($expected, ($request->all()));
    }

    /** @test */
    function it_extracts_all_post_params()
    {
        $expected = $post = [
            "foo" => "foo_val",
            "bar" => "bar_val",
        ];

        $request = new Request([], $post, []);

        $this->assertEquals($expected, ($request->all()));
    }

    /** @test
     * @throws \ReflectionException
     */
    function it_extracts_all_raw_input_data()
    {
        // mock raw input stream to fixture
        mock_static_property(Request::class, 'inputStream', $this->rawInputFixtureFile);

        $request = new Request([], [], ['CONTENT_TYPE' => 'application/json']);

        // restore raw input stream to inbuilt php
        mock_static_property(Request::class, 'inputStream', $this->phpRawInputStream);

        $expected = ["foo" => "foo_val", "bar" => "bar_val"];

        $this->assertEquals($expected, $request->all());
    }

    /** @test
     * @throws \ReflectionException
     */
    function it_should_only_extract_raw_data_when_content_type_is_json()
    {
        // mock raw input stream to fixture
        mock_static_property(Request::class, 'inputStream', $this->rawInputFixtureFile);

        // NOTE - content type is not set
        $request = new Request([], [], []);

        // restore raw input stream to inbuilt php
        mock_static_property(Request::class, 'inputStream', $this->phpRawInputStream);

        $this->assertEquals([], $request->all());
    }

    /** @test
     * @throws \ReflectionException
     */
    function it_should_not_read_form_data_if_content_type_is_json()
    {
        // mock raw input stream to fixture
        mock_static_property(Request::class, 'inputStream', $this->rawInputFixtureFile);

        $jsonData = ["foo" => "foo_val", "bar" => "bar_val"];
        $formData = ["jane" => "jane_val", "john" => "john_val"];

        $request = new Request([], $formData, ['CONTENT_TYPE' => 'application/json']);

        // restore raw input stream to inbuilt php
        mock_static_property(Request::class, 'inputStream', $this->phpRawInputStream);

        $this->assertEquals($jsonData, $request->all());
    }

    /** @test */
    function it_tells_if_request_is_json_or_not()
    {
        $request = new Request([], [], []);

        $this->assertEquals(false, $request->isJson());

        $request = new Request([], [], ['CONTENT_TYPE' => 'application/json']);

        $this->assertEquals(true, $request->isJson());
    }

    /** @test */
    function it_gets_a_single_key()
    {
        $post = [
            "foo" => "foo_val",
            "bar" => "bar_val",
        ];

        $request = new Request([], $post, []);

        $this->assertEquals('foo_val', ($request->get('foo')));
    }

    /** @test */
    function it_returns_default_value_when_key_is_missing()
    {
        $request = new Request([], [], []);

        $this->assertEquals(null, ($request->get('foo')));

        $this->assertEquals('bar', ($request->get('foo', 'bar')));
    }

    /** @test */
    function it_gets_correct_request_method()
    {
        $server = ['REQUEST_METHOD' => 'GET'];

        $request = new Request([], [], $server);

        $this->assertEquals('GET', ($request->method()));

        $server = ['REQUEST_METHOD' => 'POST'];

        $request = new Request([], [], $server);

        $this->assertEquals('POST', ($request->method()));
    }

    /** @test */
    function it_gets_the_correct_uri()
    {
        $server = ['REQUEST_URI' => '/foo/bar/?php=true'];

        $request = new Request([], [], $server);

        $this->assertEquals('foo/bar', ($request->uri()));
    }

    /** @test */
    function it_gets_the_correct_uri_for_root_routes()
    {
        $server = ['REQUEST_URI' => '/'];

        $request = new Request([], [], $server);

        $this->assertEquals('/', ($request->uri()));
    }

    /** @test */
    function capture_and_construct_result_in_similar_request_instances()
    {
        $get = ['foo' => 'bar'];
        $post = ['bar' => 'baz'];
        $server = ['REQUEST_URI' => '/foo_bar', 'REQUEST_METHOD' => 'POST',];

        $constructedRequest = new Request($get, $post, $server);

        $_GET = array_merge($_GET, $get);
        $_POST = array_merge($_POST, $post);
        $_SERVER = array_merge($_SERVER, $server);

        $capturedRequest = Request::capture();

        $this->assertEquals($constructedRequest->uri(), $capturedRequest->uri());
        $this->assertEquals($constructedRequest->all(), $capturedRequest->all());
        $this->assertEquals($constructedRequest->method(), $capturedRequest->method());
    }
}
