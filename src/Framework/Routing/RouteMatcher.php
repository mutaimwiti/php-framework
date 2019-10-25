<?php

namespace Framework\Routing;

class RouteMatcher
{
    /**
     * @var array
     */
    private $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Return false or the route action.
     *
     * @param $uri
     * @return false|RouteAction
     */
    public function match($uri)
    {
        $routes = $this->toRegex($this->routes);

        foreach ($routes as $route => $action) {
            if (preg_match_all($route, $uri, $matches)) {
                array_shift($matches); // remove full route match
                $arguments = [];

                foreach ($matches as $match) {
                    if (count($match)) {
                        $arguments[] = $match[0] == "" ? null : $match[0];
                    }
                }

                return new RouteAction($action, $arguments);
            }
        }

        return false;
    }

    protected function toRegex($routes)
    {
        $regexRoutes = [];

        foreach ($routes as $route => $action) {
            $regex = $route;

            foreach ($this->regexMappers as $mapper) {
                $regex = preg_replace($mapper[0], $mapper[1], $regex);
            }

            $regexRoutes["@^$regex$@"] = $action;
        }

        return $regexRoutes;
    }

    protected $regexMappers = [
        // replace {x with ([0-9a-zA-Z-]+)
        ['@(\{[0-9a-zA-Z-]+)+@', '([0-9a-zA-Z-]+)'],
        // remove }
        ['@\}+@', ''],
        // replace ?/ with ?/?
        ['@(\?/)+@', '?/?'],
        // make closing slash optional if it is followed by an optional argument
        ['@/\(\[0-9a-zA-Z-\]\+\)\?@', '/?([0-9a-zA-Z-]+)?'],
    ];
}
