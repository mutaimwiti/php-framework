<?php

namespace Core\Router;

use Closure;
use Core\Request;
use Core\Router\Exceptions\HTTPMethodException;
use Core\Router\Exceptions\RouteNotFoundException;

class Router
{
    protected $group = '';
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
        $route = $this->applyWrappers($uri, $action);

        $this->routes['GET'][$route->uri] = $route->action;
    }

    /**
     * @param $uri
     * @param $action
     */
    public function post($uri, $action)
    {
        $route = $this->applyWrappers($uri, $action);

        $this->routes['POST'][$route->uri] = $route->action;
    }

    /**
     * @param $name
     * @param $callback
     */
    public function namespace($name, $callback)
    {
        $oldNamespace = $this->namespace;

        $this->namespace = ltrim("$oldNamespace\\$name", '\\');

        $callback($this);

        $this->namespace = $oldNamespace;
    }


    /**
     * @param $name
     * @param $callback
     */
    public function group($name, $callback)
    {
        $oldGroup = $this->group;

        $this->group = ltrim($oldGroup . "/$name", '/');

        $callback($this);

        $this->group = $oldGroup;
    }

    /**
     * @param $uri
     * @param $action
     * @return mixed
     */
    protected function applyWrappers($uri, $action)
    {
        return (Object)[
            'uri' => $this->applyGroup($uri),
            'action' => $this->applyNamespace($action),
        ];
    }

    /**
     * @param $action
     * @return mixed
     */
    protected function applyNamespace($action)
    {
        if (is_string($action)) {
            $action = ltrim($this->namespace . "\\$action", "\\");
        }

        return $action;
    }

    /**
     * @param $uri
     * @return mixed
     */
    protected function applyGroup($uri)
    {
        return ltrim("$this->group/$uri", '/');
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
