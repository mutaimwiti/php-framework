<?php

namespace Framework;

abstract class Controller
{
    public function callAction($method, $request, $arguments = [])
    {
        return call_user_func([$this, $method], $request, $arguments);
    }
}
