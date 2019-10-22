<?php

if(!function_exists('mock_static_property')) {
    /**
     * @param $className
     * @param $propertyName
     * @param $value
     * @throws ReflectionException
     */
    function mock_static_property($className, $propertyName, $value) {
        $reflectionClass = new ReflectionClass($className);

        $property = $reflectionClass->getProperty($propertyName);

        $property->setAccessible(true);
        $property->setValue($value);
        $property->setAccessible(false);
    }
}
