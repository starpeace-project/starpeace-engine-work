<?php

namespace Spengine\initialdata;


use EcoSim\Entities\PopulationClass;

class Population
{
    private static $classes = ['low_class', 'middle_class', 'high_class'];
    private static $population = [];

    public static function get_population()
    {
        self::initialise_population();
        return self::$population;
    }

    private static function initialise_population()
    {
        foreach (self::$classes as $class) {
            self::$population[] = new PopulationClass($class);
        }
    }
}