<?php

namespace Core\Router;

use Closure;
use Core\Request;
use Core\Router\Exceptions\HTTPMethodException;
use Core\Router\Exceptions\RouteNotFoundException;

class Router
{
    protected $namespace = '';

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
        $this->routes['GET'][$uri] = $this->applyNamespace($action);
    }

    /**
     * @param $uri
     * @param $action
     */
    public function post($uri, $action)
    {
        $this->routes['POST'][$uri] = $this->applyNamespace($action);;
    }

    /**
     * @param $name
     * @param $callback
     */
    public function namespace($name, $callback)
    {
        $this->namespace = $name . '\\';

        $callback($this);

        $this->namespace = '';
    }

    /**
     * @param $action
     * @return mixed
     */
    protected function applyNamespace($action)
    {
        if (is_string($action)) {
            $action = $this->namespace . stripslashes('\\') . $action;
        }

        return $action;
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
     * @throws RouteNotFoundException
     * @throws HTTPMethodException
     */
    public function match(Request $request)
    {
        $method = $request->method();

        if (array_key_exists($method, $this->routes)) {
            $uri = $request->uri();

            $action = $this->getRoutes($method)[$uri];

            if (!$action) {
                throw new RouteNotFoundException("Route $method $uri does not exist");
            }

            return $action;
        }

        throw new HTTPMethodException("Method $method is not allowed");
    }
}
