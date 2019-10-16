<?php

namespace Core;

class Request
{
    protected $GET;
    protected $POST;
    protected $SERVER;

    protected $params = [
        'GET' => [],
        'POST' => []
    ];

    public function __construct()
    {
        $this->GET = $_GET;
        $this->POST = $_POST;
        $this->SERVER = $_SERVER;

        $this->loadParams();

        return $this;
    }

    protected function loadParams() {
        foreach ($this->GET as $key => $value) {
            $this->params['GET'][$key] = $value;
        }

        foreach ($this->POST as $key => $value) {
            $this->params['POST'][$key] = $value;
        }
    }

    /**
     * @return array
     */
    public function all()
    {
        return array_merge($this->params['GET'], $this->params['POST']);
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
        return $this->SERVER['REQUEST_METHOD'];
    }

    /**
     * @return string
     */
    public function uri()
    {
        $uri = $this->SERVER['REQUEST_URI'];

        return $uri === '/' ? $uri : trim(
            parse_url($this->SERVER['REQUEST_URI'], PHP_URL_PATH),
            '/'
        );
    }
}
