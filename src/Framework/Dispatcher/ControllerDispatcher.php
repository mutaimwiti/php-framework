<?php

namespace Framework\Dispatcher;

use Framework\Request;
use Framework\Controller;
use Framework\Dispatcher\Exceptions\ControllerActionNotFoundException;

class ControllerDispatcher {
    /**
     * @param Controller $controller
     * @param $method
     * @param Request $request
     * @return mixed
     * @throws ControllerActionNotFoundException
     */
    public function dispatch(Request $request, Controller $controller, $method) {
        if (method_exists($controller, $method)) {
            return $controller->callAction($method, $request);
        } else {
            $class = get_class($controller);
            throw new ControllerActionNotFoundException(
                "Controller action $class@$method does not exist"
            );
        }
    }
}
