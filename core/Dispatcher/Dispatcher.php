<?php

namespace Core\Dispatcher;

use Closure;
use Exception;
use Core\Request;
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
    protected $controllerNamespace = 'App\\Controllers';
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
                return $this->dispatchClosureAction($request, $action);
            } else if (gettype($action) === 'string') {
                $controllerAction = $this->parseControllerAction($action);

                return $this->dispatchControllerAction($request, ...$controllerAction);
            } else {
                throw new InvalidRouteActionException("Invalid route action $action");
            }
        } catch (Exception $exception) {
            throw $exception;
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

    /**
     * @param Request $request
     * @param Closure $action
     * @return mixed
     */
    protected function dispatchClosureAction(Request $request, Closure $action)
    {
        return call_user_func($action, $request);
    }


    public function setControllerNameSpace($namespace) {
        $this->controllerNamespace = $namespace;
    }

    /**
     * @param Request $request
     * @param string $controller
     * @param string $method
     * @return mixed
     * @throws ControllerNotFoundException
     * @throws ControllerActionNotFoundException
     */
    protected function dispatchControllerAction(Request $request, string $controller, string $method)
    {
        $class = "$this->controllerNamespace\\$controller";

        if (class_exists($class)) {
            $controllerInstance = new $class;

            return $this->controllerDispatcher->dispatch($request, $controllerInstance, $method);
        } else {
            throw new ControllerNotFoundException(
                "Controller $class does not exist"
            );
        }
    }
}