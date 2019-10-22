<?php

namespace Framework\Router\Exceptions;

use Exception;
use Throwable;

class RouteNotFoundException extends Exception {
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
