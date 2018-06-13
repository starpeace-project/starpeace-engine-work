<?php
/**
 * Created by PhpStorm.
 * User: ronaldappleton
 * Date: 13/06/2018
 * Time: 20:25
 */

namespace Spengine\initialdata;

use EcoSim\Entities\Farm;

class Businesses
{
    private static $businesses = [];

    public static function get_businesses()
    {
        self::initialise_businesses();
        return self::$businesses;
    }

    private static function initialise_businesses()
    {
        $farm1 = new Farm('Rons Farm', 'low_class');
        self::$businesses[] = $farm1;

        $farm2 = new Farm('Bills Farm', 'low_class');
        self::$businesses[] = $farm2;

        $farm3 = new Farm('Bobs Farm', 'low_class');
        self::$businesses[] = $farm3;

        $farm4 = new Farm('Damians Farm', 'low_class');
        self::$businesses[] = $farm4;
    }
}