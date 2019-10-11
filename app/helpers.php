<?php

if (!function_exists('dump')) {
    /**
     * @param $val
     */
    function dump($val)
    {
        var_dump($val);
    }
}

if (!function_exists('dd')) {
    /**
     * @param $val
     */
    function dd($val)
    {
        var_dump($val);

        die();
    }
}
