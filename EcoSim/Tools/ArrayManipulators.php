<?php
/**
 * Created by PhpStorm.
 * User: ronaldappleton
 * Date: 22/05/2018
 * Time: 00:36
 */

namespace EcoSim\Tools;


class ArrayManipulators
{
    public static function setArrayValues($array, $values, $wipe=false)
    {
        if (!empty($wipe) && is_array($values)) {
            return $values;
        }

        if (is_string($values)) {
            return false;
        }

        foreach ($values as $key => $value) {
            $array[$key] = $value;
        }
        return $array;
    }
}