<?php

if (!function_exists('getArray')) {
    function getArray($values)
    {
        if (!is_array($values) || !is_string($values)) {
            return false;
        }

        if (is_array($values)) {
            return $values;
        }

        if (is_string($values)) {
            return [$values];
        }
    }
}

if (!function_exists('rand_float')) {
    function float_rand($Min, $Max, $round=0){
        //validate input
        if ($Min>$Max) { $min=$Max; $max=$Min; }
        else { $min=$Min; $max=$Max; }
        $randomfloat = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        if($round>0)
            $randomfloat = round($randomfloat,$round);

        return $randomfloat;
    }
}

if (!function_exists('rand_average_percentage')) {
    function rand_average_percentage() {
        return float_rand(4, 9);
    }
}

if (!function_exists('debug')) {
    function debug($debug=true, $message) {
        if($debug) {
            echo $message . PHP_EOL;
        }

    }
}

if (!function_exists('array_pull_flatter')) {
    function array_pull_flatter(&$array) {
        $new_array = [];
        foreach ($array as $business) {
            $key = key($business);
            $new_array[$key] = $business[$key];
        }
        $array = $new_array;
    }
}
