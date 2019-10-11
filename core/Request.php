<?php

namespace Core;

use Exception;

class Request {
    protected $params = [
        'GET' => [],
        'POST' => []
    ];

    public function __construct()
    {
        foreach ($_GET as $key => $value) {
            $this->params['GET'][$key] = $value;
        }

        foreach ($_POST as $key => $value) {
            $this->params['POST'][$key] = $value;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function all() {
        return array_merge($this->params['GET'], $this->params['POST']);
    }

    /**
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    public function get($key) {
        $value = ($this->all())[$key];

        if ($value) {
            return $value;
        }

        throw new Exception('Request parameter not found.');
    }

    /**
     * @return string
     */
    public function method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return string
     */
    public function uri() {
        return trim(
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
            '/'
        );
    }
}
