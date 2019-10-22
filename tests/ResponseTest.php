<?php

namespace Tests;

use TypeError;
use Framework\Response;

class ResponseTest extends TestCase
{
    /** @test */
    public function it_allows_constructor_initialization_of_content_status_and_headers()
    {
        $status = 405;
        $content = "Hello World";
        $headers = ['X-test-1' => 'V1', 'X-test-2' => 'V2',];

        $response = new Response($content, $status, $headers);

        $this->assertEquals($status, $response->status);
        $this->assertEquals($content, $response->content);
        $this->assertEquals($headers, array_intersect_assoc($response->headers, $headers));
    }

    /** @test */
    public function it_allows_setting_of_headers()
    {
        $response = new Response('Bad request', 400);
        $response
            ->header('X-test-header1', 'test-header1')
            ->header('X-test-header2', 'test-header2');

        $sent = [
            'X-test-header1' => 'test-header1',
            'X-test-header2' => 'test-header2'
        ];

        $this->assertEquals($sent, array_intersect_assoc($response->headers, $sent));
    }

    /** @test */
    public function it_throws_if_status_is_not_an_integer()
    {
        $this->expectException(TypeError::class);

        $status = "Some string";

        new Response([], $status);
    }

    /**
     * @test
     */
    public function it_automatically_sets_application_json_header_for_array_content()
    {
        $data = ['cr7' => 'Cristiano'];

        $response = new Response($data);

        $this->assertEquals(Response::APPLICATION_JSON, $response->headers['Content-Type']);
    }

    /**
     * @test
     */
    public function it_automatically_sets_text_html_header_for_non_array_content()
    {
        $data = 'Hello World';

        $response = new Response($data);

        $this->assertEquals(Response::TEXT_HTML, $response->headers['Content-Type']);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_allows_automatically_converts_array_content_to_json()
    {
        $data = ['cr7' => 'Cristiano'];

        $response = new Response($data);

        ob_start();

        $response->send();

        $sent = ob_get_clean();

        $this->assertEquals($data, json_decode($sent, true));
    }


    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_sends_all_headers()
    {
        $response = new Response('Hello world');

        $response->header('X-foo', 'Foo');
        $response->header('X-bar', 'Bar');

        $response->sendHeaders();

        $this->markTestIncomplete('TODO: find a proper solution for getting sent headers');
//        $this->assertContains('X-foo', headers_list());
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_uses_200_as_the_default_status_code()
    {
        $response = new Response([]);

        ob_start();

        $response->send();

        ob_end_clean();

        $this->assertEquals(200, http_response_code());
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_sends_the_correct_status_code()
    {
        $response = new Response([], 500);

        ob_start();

        $response->send();

        ob_end_clean();

        $this->assertEquals(500, http_response_code());
    }

    /**
     * @test
     */
    public function send_triggers_both_send_headers_and_send_content()
    {
        $mock = $this->getMockBuilder(Response::class)
            ->setMethods(array('sendHeaders', 'sendContent'))
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('sendHeaders');

        $mock->expects($this->once())
            ->method('sendContent');

        $mock->send();
    }
}
