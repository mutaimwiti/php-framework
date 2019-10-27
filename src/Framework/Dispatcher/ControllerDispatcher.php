<?php

namespace Framework\Dispatcher;

use Framework\Request;
use Framework\Controller;
use Framework\Dispatcher\Exceptions\ControllerActionNotFoundException;

class ControllerDispatcher
{
    /**
     * @param Request $request
     * @param Controller $controller
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws ControllerActionNotFoundException
     */
    public function dispatch(Request $request, Controller $controller, $method, $arguments = [])
    {
        if (method_exists($controller, $method)) {
            return $controller->callAction($method, $request, $arguments);
        } else {
            $class = get_class($controller);
            throw new ControllerActionNotFoundException(
                "Controller action $class@$method does not exist"
            );
        }
    }
}
