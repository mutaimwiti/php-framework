<?php

namespace Framework;

abstract class Controller
{
    public function callAction($method, $request)
    {
        return call_user_func([$this, $method], $request);
    }
}