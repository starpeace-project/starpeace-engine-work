<?php
/**
 * Created by PhpStorm.
 * User: ronaldappleton
 * Date: 13/06/2018
 * Time: 20:33
 */

namespace Spengine\initialdata;


use EcoSim\Entities\Residential;

class Residentials
{
    private static $residentials = [];

    public static function get_residentials()
    {
        self::initialise_residentials();
        return self::$residentials;
    }

    private static function initialise_residentials()
    {
        $ronsApartments = new Residential('Rons Apartments', 'low_class', 30);
        self::$residentials[] = $ronsApartments;

        $billsApartments = new Residential('Bills Apartments', 'low_class', 40);
        self::$residentials[] = $billsApartments;

        $bobsApartments = new Residential('Bobs Apartments', 'middle_class', 20);
        self::$residentials[] = $bobsApartments;

        $bensApartments = new Residential('Bens Apartments', 'high_class', 10);
        self::$residentials[] = $bensApartments;
    }
}