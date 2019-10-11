<?php

namespace Core;

use Closure;
use Exception;

class Router
{
    protected $routes = [
        'POST' => [],
        'GET' => [],
    ];

    /**
     * @param $uri
     * @param $action
     */
    public function get($uri, $action)
    {
        $this->routes['GET'][$uri] = $action;
    }

    /**
     * @param $uri
     * @param $action
     */
    public function post($uri, $action)
    {
        $this->routes['POST'][$uri] = $action;
    }

    /**
     * @param null $method
     * @return array|null
     */
    public function getRoutes($method = null)
    {
        return $method ? $this->routes[$method] : $this->routes;
    }

    /**
     * @param Request $request
     * @return bool|string|Closure
     * @throws Exception
     */
    public function match(Request $request)
    {
        $method = $request->method();

        if (array_key_exists($method, $this->routes)) {
            $uri = $request->uri();

            $action = $this->getRoutes($method)[$uri];

            if (!$action) {
                throw new Exception("Route $method $uri does not exist");
            }

            return $action;
        }

        throw new Exception("Method $method is not allowed");
    }
}
