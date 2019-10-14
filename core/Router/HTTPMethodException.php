<?php

namespace Core\Router;

use Throwable;

class HTTPMethodException extends \Exception {
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}