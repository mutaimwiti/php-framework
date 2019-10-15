<?php

if (!function_exists('dump')) {
    /**
     * @param array $values
     */
    function dump(...$values)
    {
        foreach ($values as $value) {
            print_r("$value\n");
        }
    }
}

if (!function_exists('dd')) {
    /**
     * @param array $values
     */
    function dd(...$values)
    {
        foreach ($values as $value) {
            print_r("$value\n");
        }

        die();
    }
}
