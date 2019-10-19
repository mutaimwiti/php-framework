<?php

namespace Core\Dispatcher;

use Closure;
use Exception;
use Core\Request;
use Core\Response;
use Core\Router\Router;
use Core\Dispatcher\Exceptions\InvalidRouteActionException;
use Core\Dispatcher\Exceptions\ControllerNotFoundException;
use Core\Dispatcher\Exceptions\ControllerActionNotFoundException;


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
            $action = $this->router->match($request);

            if ($action instanceof Closure) {
                $response = $this->dispatchClosureAction($request, $action);
            } else if (gettype($action) === 'string') {
                $response = $this->dispatchControllerAction($request, $action);
            } else {
                throw new InvalidRouteActionException("Invalid route action $action");
            }

            return $response instanceof Response ? $response : response($response);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param Request $request
     * @param Closure $action
     * @return mixed
     */
    protected function dispatchClosureAction(Request $request, Closure $action)
    {
        return call_user_func($action, $request);
    }

    /**
     * @param Request $request
     * @param string $action
     * @return mixed
     * @throws ControllerActionNotFoundException
     * @throws ControllerNotFoundException
     * @throws InvalidRouteActionException
     */
    protected function dispatchControllerAction(Request $request, string $action)
    {
        [$controller, $method] = $this->parseControllerAction($action);

        if (class_exists($controller)) {
            $controllerInstance = new $controller;

            return $this->controllerDispatcher->dispatch($request, $controllerInstance, $method);
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
