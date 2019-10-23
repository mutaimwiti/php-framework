<?php

namespace Framework\Dispatcher;

use Closure;
use Exception;
use Framework\Request;
use Framework\Response;
use Framework\Routing\Router;
use Framework\Routing\RouteAction;
use Framework\Dispatcher\Exceptions\InvalidRouteActionException;
use Framework\Dispatcher\Exceptions\ControllerNotFoundException;
use Framework\Dispatcher\Exceptions\ControllerActionNotFoundException;


class Dispatcher
{
    /**
     * @var Router
     */
    protected $router;
    /**
     * @var ControllerDispatcher
     */
    private $controllerDispatcher;

    public function __construct(Router $router, ControllerDispatcher $controllerDispatcher)
    {
        $this->router = $router;
        $this->controllerDispatcher = $controllerDispatcher;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request)
    {
        try {
            $routeAction = $this->router->match($request);

            $handler = $routeAction->handler;

            if ($handler instanceof Closure) {
                $response = $this->dispatchClosureAction($request, $routeAction);
            } else if (is_string($handler)) {
                $response = $this->dispatchControllerAction($request, $routeAction);
            } else {
                throw new InvalidRouteActionException("Invalid route action $handler");
            }

            return $response instanceof Response ? $response : response($response);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param Request $request
     * @param RouteAction $routeAction
     * @return mixed
     */
    protected function dispatchClosureAction(Request $request, RouteAction $routeAction)
    {
        return call_user_func($routeAction->handler, $request, ...$routeAction->arguments);
    }

    /**
     * @param Request $request
     * @param RouteAction $routeAction
     * @return mixed
     * @throws ControllerActionNotFoundException
     * @throws ControllerNotFoundException
     * @throws InvalidRouteActionException
     */
    protected function dispatchControllerAction(Request $request, RouteAction $routeAction)
    {
        [$controller, $method] = $this->parseControllerAction($routeAction->handler);

        if (class_exists($controller)) {
            $controllerInstance = new $controller;

            return $this->controllerDispatcher->dispatch(
                $request,
                $controllerInstance,
                $method,
                $routeAction->arguments
            );
        } else {
            throw new ControllerNotFoundException(
                "Controller $controller does not exist"
            );
        }
    }

    /**
     * @param string $actionString
     * @return array
     * @throws InvalidRouteActionException
     */
    protected function parseControllerAction(string $actionString)
    {
        $actionParts = explode('@', $actionString);

        if (count($actionParts) !== 2) {
            throw new InvalidRouteActionException(
                "Invalid controller action $actionString"
            );
        }

        return $actionParts;
    }
}
