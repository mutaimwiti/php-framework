<?php

if (!function_exists('dump')) {
    function dump()
    {
        array_map(function ($value) {
            echo '<pre>';
            print_r($value);
            echo '</pre>';
        }, func_get_args());
    }
}

if (!function_exists('dd')) {
    function dd()
    {
        dump(...func_get_args());
        die();
    }
}

if (!function_exists('response')) {
    function response($content, $status = 200, $headers = [])
    {
        return new \Core\Response($content, $status, $headers);
    }
}
