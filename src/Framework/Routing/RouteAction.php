<?php

namespace Framework\Routing;

/**
 * Class Route
 * @package Framework\Routing
 * @property-read string $handler
 * @property-read string $arguments
 */
class RouteAction
{
    protected $action;
    protected $arguments;

    public function __construct($handler, $arguments = [])
    {
        $this->handler = $handler;
        $this->arguments = $arguments;
    }

    /**
     * @param $property
     * @return mixed|null
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return null;
    }
}
