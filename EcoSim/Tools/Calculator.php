<?php

namespace EcoSim\Tools;


class Calculator
{
    private static $max_population_desire_percentage = 20;

    private static $max_residential_desire_percentage = 20;

    public static function calculatePopulationDesire($classes, $businesses)
    {
        $populationRequiredTotals = [];
        $populationCurrentTotals = [];
        $classDesires = [];

        foreach ($classes as $class) {
            foreach ($businesses as $business) {
                if (!empty($populationRequiredTotals[$class])) {
                    $populationRequiredTotals[$class] += $business->getRequiredWorkers($class);
                } else {
                    $populationRequiredTotals[$class] = $business->getRequiredWorkers($class);
                }
                if (!empty($populationCurrentTotals[$class])) {
                    $populationCurrentTotals[$class] += $business->getCurrentWorkers($class);
                } else {
                    $populationCurrentTotals[$class] = $business->getCurrentWorkers($class);
                }
            }
            $requiredAverage = self::getArrayAverage($populationRequiredTotals);
            $currentAverage = self::getArrayAverage($populationRequiredTotals);
            $classDesires[$class] = self::getPercentage($requiredAverage, $currentAverage);
        }

        foreach ($classDesires as &$classDesire) {
            $classDesire = self::getPercentage(self::$max_population_desire_percentage, $classDesire);
        }

        return $classDesires;
    }

    public static function calculateResidentialPopulationDesire($classes, $residentials)
    {
        $populationRequiredTotals = [];
        $populationCurrentTotals = [];
        $classDesires = [];

        foreach ($classes as $class) {
            if (!empty($residentials['class'])) {
                foreach ($residentials[$class] as $residential) {
                    if ($residential->getClass() == $class) {
                        if (!empty($populationRequiredTotals[$class])) {
                            $populationRequiredTotals[$class] += $residential->getPopulationLimit();
                        } else {
                            $populationRequiredTotals[$class] = $residential->getPopulationLimit();
                        }
                        if (!empty($populationCurrentTotals[$class])) {
                            $populationCurrentTotals[$class] += $residential->getPopulation();
                        } else {
                            $populationCurrentTotals[$class] = $residential->getPopulation();
                        }
                    }
                }
                $requiredAverage = self::getArrayAverage($populationRequiredTotals);
                $currentAverage = self::getArrayAverage($populationRequiredTotals);
                $classDesires[$class] = self::getPercentage($requiredAverage, $currentAverage);
            }
        }
        foreach ($classDesires as &$classDesire) {
            $classDesire = self::getPercentage(self::$max_residential_desire_percentage, $classDesire);
        }

        return $classDesires;
    }

    /**
     * @param int $max_population_desire_percentage
     */
    public static function setMaxPopulationDesirePercentage(int $max_population_desire_percentage): void
    {
        self::$max_population_desire_percentage = $max_population_desire_percentage;
    }

    /**
     * @param int $max_residential_desire_percentage
     */
    public static function setMaxResidentialDesirePercentage(int $max_residential_desire_percentage): void
    {
        self::$max_residential_desire_percentage = $max_residential_desire_percentage;
    }

    public static function getArrayAverage($array)
    {
        $array = array_filter($array);
        return array_sum($array) / count($array);
    }

    public static function getPercentage($testValue, $maxValue)
    {
        $one_percent = $maxValue / 100;
        $percentage = $testValue / $one_percent;
        return $percentage;
    }

    public static function takePercentageOfWhole($taker, $total) {
        return ($total / 100) * $taker;
    }
}