<?php

namespace Framework\Routing;

use Closure;
use Framework\Request;
use Framework\Routing\Exceptions\HTTPMethodException;
use Framework\Routing\Exceptions\RouteNotFoundException;

/**
 * Class Router
 * @package Framework\Router
 * @property-read array $routes
 */
class Router
{
    protected $prefix = '';
    protected $namespace = '';

    protected $routes = [
        'POST' => [],
        'GET' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => [],
    ];

    /**
     * @param $uri
     * @param $action
     */
    public function get($uri, $action)
    {
        $this->registerRoute('GET', $uri, $action);
    }

    /**
     * @param $uri
     * @param $action
     */
    public function post($uri, $action)
    {
        $this->registerRoute('POST', $uri, $action);
    }

    /**
     * @param $uri
     * @param $action
     */
    public function put($uri, $action)
    {
        $this->registerRoute('PUT', $uri, $action);
    }

    /**
     * @param $uri
     * @param $action
     */
    public function patch($uri, $action)
    {
        $this->registerRoute('PATCH', $uri, $action);
    }

    /**
     * @param $uri
     * @param $action
     */
    public function delete($uri, $action)
    {
        $this->registerRoute('DELETE', $uri, $action);
    }

    /**
     * Register route. Add all wrappers before registering.
     *
     * @param $method
     * @param $uri
     * @param $action
     */
    protected function registerRoute($method, $uri, $action) {
        $route = $this->applyWrappers($uri, $action);

        $this->routes[$method][$route->uri] = $route->action;
    }

    protected function extendNamespace($name)
    {
        $this->namespace = ltrim("$this->namespace\\$name", '\\');
    }

    protected function extendPrefix($prefix)
    {
        $this->prefix = ltrim($this->prefix . "/$prefix", '/');
    }

    /**
     * @param $name
     * @param $callback
     */
    public function namespace($name, $callback)
    {
        $oldNamespace = $this->namespace;

        $this->extendNamespace($name);

        $callback($this);

        $this->namespace = $oldNamespace;
    }


    /**
     * @param $prefix
     * @param $callback
     */
    public function prefix($prefix, $callback)
    {
        $oldPrefix = $this->prefix;

        $this->extendPrefix($prefix);

        $callback($this);

        $this->prefix = $oldPrefix;
    }

    /**
     * @param array $options
     * @param $callback
     */
    public function group($options, $callback)
    {
        $oldPrefix = $this->prefix;
        $oldNamespace = $this->namespace;

        foreach ($options as $option => $value) {
            switch ($option) {
                case 'prefix':
                    $this->extendPrefix($value);
                    break;
                case 'namespace':
                    $this->extendNamespace($value);
            }
        }

        $callback($this);

        $this->prefix = $oldPrefix;
        $this->namespace = $oldNamespace;
    }

    /**
     * @param $uri
     * @param $action
     * @return mixed
     */
    protected function applyWrappers($uri, $action)
    {
        return (Object)[
            'uri' => $this->applyPrefix($uri),
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
    protected function applyPrefix($uri)
    {
        $uri = ltrim("$this->prefix/$uri", '/');

        return $uri === '' ? '/' : trim($uri, '/');
    }

    /**
     * @param $property
     * @return array|null
     */
    public function __get($property)
    {
        return $property === 'routes' ? $this->routes : null;
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

            if (isset($this->routes[$method][$uri])) {
                $action = $this->routes[$method][$uri];
            } else {
                throw new RouteNotFoundException("Route $method $uri does not exist");
            }

            return $action;
        }

        throw new HTTPMethodException("Method $method is not allowed");
    }
}
