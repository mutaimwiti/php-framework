<?php

namespace Core;

class Request
{
    protected $get;
    protected $post;
    protected $server;

    protected $data = [];
    protected $query = [];

    /**
     * Request constructor.
     * Requires PHP super globals.
     *
     * @param $get
     * @param $post
     * @param $server
     */
    public function __construct($get, $post, $server)
    {
        $this->get = $get;
        $this->post = $post;
        $this->server = $server;

        $this->loadParams();

        return $this;
    }

    /**
     * Load parameters instance super globals GET and POST.
     */
    protected function loadParams()
    {
        foreach ($this->get as $key => $value) {
            $this->query[$key] = $value;
        }

        foreach ($this->post as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    /**
     * Create a request instance automatically from PHP super globals.
     *
     * @return Request
     */
    public static function capture()
    {
        return new static(
            $_GET,
            $_POST,
            $_SERVER
        );
    }

    /**
     * Simulate a request - for the purpose of testing.
     *
     * @param string $uri
     * @param string $method
     * @param array $parameters
     * @return Request
     */
    public static function create(string $uri = '/', string $method = 'GET', array $parameters = [])
    {
        $server['REQUEST_URI'] = $uri;
        $server['REQUEST_METHOD'] = $method;
        $get = [];
        $post = [];

        switch ($method) {
            case 'GET':
                $get = $parameters;
                break;
            case 'POST':
                $post = $parameters;
                break;
        }

        return new static(
            $get,
            $post,
            $server
        );
    }

    /**
     * @return array
     */
    public function all()
    {
        return array_merge($this->query, $this->data);
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $all = $this->all();

        if (array_key_exists($key, $all)) {
            return $all[$key];
        }

        return $default;
    }

    /**
     * @return string
     */
    public function method()
    {
        return $this->server['REQUEST_METHOD'];
    }

    /**
     * @return string
     */
    public function uri()
    {
        $uri = $this->server['REQUEST_URI'];

        return $uri === '/' ? $uri : trim(
            parse_url($this->server['REQUEST_URI'], PHP_URL_PATH),
            '/'
        );
    }
}
