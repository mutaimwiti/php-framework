<?php

namespace Framework;

class Request
{
    protected $get;
    protected $post;
    protected $server;

    protected $data = [];
    protected $query = [];

    protected static $inputStream = 'php://input';

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
        $this->loadRaw();

        return $this;
    }

    /**
     * Load parameters from super globals GET and POST.
     */
    protected function loadParams()
    {
        foreach ($this->get as $key => $value) {
            $this->query[$key] = $value;
        }

        // if we have a json request we will not load POST parameters
        if (!$this->isJson()) {
            foreach ($this->post as $key => $value) {
                $this->data[$key] = $value;
            }
        }
    }

    /**
     * Load raw input.
     */
    protected function loadRaw()
    {
        if ($this->isJson()) {
            $body = file_get_contents(self::$inputStream);

            $data = json_decode($body, true);

            if (!is_array($data)) {
                $data = [];
            }

            foreach ($data as $key => $value) {
                $this->data[$key] = $value;
            }
        }
    }

    /**
     * Check if request is JSON.
     * @return bool
     */
    public function isJson()
    {
        return isset($this->server['CONTENT_TYPE']) &&
            (stripos($this->server['CONTENT_TYPE'], 'application/json') !== false);
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
