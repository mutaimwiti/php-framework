<?php

namespace Acme\Dispatcher\Exceptions;

use Exception;
use Throwable;

class ControllerNotFoundException extends Exception {
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
