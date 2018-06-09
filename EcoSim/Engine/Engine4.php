<?php

namespace EcoSim\Engine;

use EcoSim\Entities\Product;
use EcoSim\Entities\Farm;
use EcoSim\Tools\Calculator;
use jc21\CliTable;

class Engine4
{
    const DEBUG = false;

    const ROUND_DELAY = 0.2;
    const POPULATION_TICK = 24;

    const DISPLAY_OUTPUT = true;
    const DISPLAY_COLUMNS = ['current', 'required'];

    private $round_counter = 0;
    private $tick_counter = 1;
    private $last_table_colour = '';

    private $game_products = null;
    private $game_businesses = null;

    /**
     * Population Variables
     */
    private $populationClasses = ['low_class', 'middle_class', 'high_class'];
    private $populationPool = [];
    private $populationNeeded = [];
    private $populationInflux = [];
    private $classAveragePayLevel = [];

    /**
     * Business Variables
     */
    private $businessCountWorkersNeededByClass = [];

    private $trade_centre = null;

    private $roundPercentageIncrease = [];

    private $populationIncreased = false;

    private $businessJobDesirability = [];

    /**
     * Job Related Variables
     */
    /*
     * Expected Pay levels Per Hour
     */

    public function __construct()
    {
        $this->game_products['chemicals'] = new Product('chemical', 4);
        $this->game_products['seeds'] = new Product('seeds', 1, 200);
        $this->game_products['movie'] = new Product('movie', 12);
        $this->game_products['clothes'] = new Product('clothes', 10);
        $this->game_products['processed_food'] = new Product('processed_food', 2);
        $this->game_products['fresh_food'] = new Product('fresh_food', 1);
        $this->game_products['organic_materials'] = new Product('organic_materials', 3);
    }

    public function setupEngine()
    {
        $this->setupTradeCentre();
        $this->setupBusinesses();
        foreach ($this->populationClasses as $class) {
            $this->classAveragePayLevel[$class] = 1;
        }
    }

    public function initialisePopulationVariables()
    {
        // Initialise Population pools..
        foreach ($this->populationClasses as $class) {
            $this->populationPool[$class] = 0;
            $this->populationInflux[$class] = 0;
            $this->populationNeeded[$class] = 0;
        }
    }

    public function initialiseBusinessVariables()
    {
        foreach ($this->populationClasses as $class) {
            $this->businessCountWorkersNeededByClass[$class] = 0;
            foreach ($this->game_businesses as $name => $business) {
                $this->businessJobDesirability[$name][$class] = 0;
            }
        }
        $this->calculateBusinessJobDesirability();
    }

    private function calculateBusinessJobDesirability()
    {
        // At the moment pay average
        foreach ($this->game_businesses as $name => $business) {
            foreach ($this->populationClasses as $class) {
                $wage_level = $business->getWageLevel($class);
                $desirability = ($wage_level / ($this->classAveragePayLevel[$class] * 2)) * 100;
                $this->businessJobDesirability[$name][$class] = $desirability;
            }
        }
    }

    private function calculateAveragePayLevels() {
        foreach ($this->populationClasses as $class) {
            $pay_level_array = [];
            foreach ($this->game_businesses as $business) {
                $pay_level_array[] = $business->getWageLevel($class);
            }
            $pay_level_array = array_filter($pay_level_array);
            $average = array_sum($pay_level_array)/count($pay_level_array);
            $this->classAveragePayLevel[$class] = $average;
            debug(self::DEBUG, "Average pay level for {$class} is {$average}");
        }
    }

    private function getBusinessClassPopulationSharePercentage($businessName, $class) {
        $thisBusinessJobDesirability = $this->businessJobDesirability[$businessName][$class];

        $desTotal = 0;
        foreach ($this->game_businesses as $name => $business) {
            $desTotal += $this->businessJobDesirability[$name][$class];
        }
        $one_percent = $desTotal / 100;
        return $thisBusinessJobDesirability / $one_percent;
    }

    public function resetEngineVariables()
    {
        $this->populationIncreased = false;
        $this->initialisePopulationVariables();
        $this->initialiseBusinessVariables();
    }

    private function setupTradeCentre()
    {
        $products = [
            'chemicals' => 100,
            'seeds' => 20,
            'movie' => 200,
            'clothes' => 50
        ];

        foreach ($products as $product => $quantity) {
            $this->trade_centre[$product] = $quantity;
        }
    }

    public function setupBusinesses()
    {
        // We will start with two farms
        $this->game_businesses['farm1'] = new Farm();
        $this->game_businesses['farm2'] = new Farm();
        $this->game_businesses['farm2']->setWageLevel('low_class', 3);
    }

    public function setRoundPopulationPercentageIncreases()
    {
        $this->roundPercentageIncrease = Calculator::calculatePopulationDesire($this->populationClasses, $this->game_businesses);
    }

    public function runPopulationInflux()
    {
        foreach ($this->game_businesses as $business) {
            foreach ($this->populationClasses as $class) {
                if (!empty($business->getRequiredWorkers($class))) {
                    return true;
                }
            }
        }

        return false;
    }

    public function increasePopulation()
    {
        foreach ($this->populationClasses as $class) {
            if (!empty($this->populationNeeded[$class])) {
                if (!empty($this->roundPercentageIncrease[$class])) {
                    $amountToIncrease = ($this->populationNeeded[$class] / 100) * $this->roundPercentageIncrease[$class];
                    $amountToIncrease = round($amountToIncrease, 0, PHP_ROUND_HALF_UP);
                    if (empty($amountToIncrease)) {
                        if (!empty($this->businessCountWorkersNeededByClass[$class])) {
                            $this->populationPool[$class]++;
                        }
                    }
                    $this->populationPool[$class] += $amountToIncrease;
                    $this->populationIncreased = true;
                }
            }
        }
    }

    public function setPopulationNeeded()
    {
        foreach ($this->game_businesses as $business) {
            foreach ($this->populationClasses as $class) {
                if (!empty($needed = $business->getRequiredWorkers($class))) {
                    $this->populationNeeded[$class] += $needed;
                    $this->businessCountWorkersNeededByClass[$class]++;
                }
            }
        }
    }

    public function processPopulationNeeded()
    {
        foreach ($this->populationClasses as $class) {
            if (!empty($this->populationNeeded[$class])) {
                if (!empty($this->roundPercentageIncrease[$class])) {
                    $amountToIncrease = ($this->populationNeeded[$class] / 100) * $this->roundPercentageIncrease[$class];
                    $amountToIncrease = round($amountToIncrease, 0, PHP_ROUND_HALF_UP);
                    if (empty($amountToIncrease)) {
                        if (!empty($this->businessCountWorkersNeededByClass[$class])) {
                            $randomNumber = mt_rand(0, 100);
                            if ($randomNumber >= 90) {
                                $this->populationPool[$class]++;
                            }
                        }
                    }
                    $this->populationPool[$class] += $amountToIncrease;
                    $this->populationInflux[$class] += $amountToIncrease;
                    $this->populationIncreased = true;
                }
            }
        }
    }

    public function distributePopulationPool()
    {
        if ($this->populationIncreased) {
            foreach ($this->game_businesses as $name => $business) {
                debug(self::DEBUG,"Getting population for {$name}");
                foreach ($this->populationClasses as $class) {
                    debug(self::DEBUG,"Getting population for {$class}");
                    if (!empty($this->populationPool[$class])) {
                        debug(self::DEBUG,"Population pool is not empty");
                        if (!empty($business->getRequiredWorkers($class)) && $business->getRequiredWorkers($class) > 0) {
                            debug(self::DEBUG,"{$name} does need {$class} workers");
                            $thisBusinessSharePercentage = $this->getBusinessClassPopulationSharePercentage($name, $class);
                            debug(self::DEBUG,"{$name} should take {$thisBusinessSharePercentage}% of the pool");
                            $populationInPool = $this->populationPool[$class];
                            $popInfluxThisRound = $this->populationInflux[$class];
                            debug(self::DEBUG,"The population influx of {$class} workers this round was {$popInfluxThisRound}");
                            debug(self::DEBUG,"The population pool has {$populationInPool} {$class} workers in");
                            $share = round(($thisBusinessSharePercentage / 100) * $popInfluxThisRound);
                            debug(self::DEBUG,"{$name} is taking {$share} workers from the pool");
                            while (!empty($share) && $share > $this->populationPool[$class]) {
                                debug(self::DEBUG,"Share ({$share}) is larger than the pool ({$populationInPool})");
                                $share--;
                            }
                            if ($this->populationPool[$class] == 1 && empty($share)) {
                                $share = 1;
                            }
                            if ($share > $business->getRequiredWorkers($class)) {
                                $share = $business->getRequiredWorkers($class);
                            }
                            debug(self::DEBUG,"Adding {$share} {$class} workers to {$name}");
                            $business->setCurrentWorkers($class, $share);
                            debug(self::DEBUG,"Removing {$share} {$class} from {$name} jobs required");
                            $business->reduceJobsRequired($class, $share);
                            debug(self::DEBUG,"Reducing the {$class} population pool by {$share}");
                            $this->populationPool[$class] -= $share;
                        }
                    }
                }
            }
        }
    }


    public function processRound()
    {
        $this->resetEngineVariables();
        if ($this->tick_counter == 24 && $this->runPopulationInflux()) {
            $this->calculateAveragePayLevels();
            $this->setRoundPopulationPercentageIncreases();
            $this->setPopulationNeeded();
            $this->processPopulationNeeded();
            $this->distributePopulationPool();
            if (self::DISPLAY_OUTPUT) {
                $this->outputBusinessPopulationTable($this->getBusinessPopulationData());
            }
        }

        if ($this->tick_counter == 24) {
            $this->tick_counter = 1;
        } else {
            $this->tick_counter++;
        }
    }

    private function getColor($old)
    {
        $colours = ['blue', 'green', 'cyan', 'magenta'];
        do {
            $colour = $colours[mt_rand(0, count($colours) - 1)];
        } while ($colour == $old);

        return $colour;
    }

    public function outputBusinessPopulationTable($data)
    {

        $this->last_table_colour = $table_colour = $this->getColor($this->last_table_colour);

        $table = new CliTable();
        $table->setTableColor($table_colour);
        $table->setHeaderColor('yellow');
        $table->addField('Round', 'round', false, 'white');
        $table->addField('Business Name', 'business_name', false, 'white');
        foreach (self::DISPLAY_COLUMNS as $column) {
            foreach ($this->populationClasses as $class) {
                $abv_parts = explode('_', $class);
                foreach ($abv_parts as &$abv_part) {
                    $abv_part = ucfirst($abv_part);
                }
                $abv = implode(' ', $abv_parts);
                $table->addField(ucfirst($column) . ' ' . $abv, $column . '_' . $class, false, 'white');
            }
        }
        $table->addField('Population', 'population', false, 'white');
        $table->injectData($data);
        $table->display();
    }

    public function getBusinessPopulationData()
    {
        $data = [];

        foreach ($this->game_businesses as $name => $object) {
            $this_business_data = [];
            $this_business_data['round'] = $this->round_counter;
            $this_business_data['business_name'] = $name;
            foreach (self::DISPLAY_COLUMNS as $column) {
                foreach ($this->populationClasses as $class) {
                    $method = 'get' . ucfirst($column) . 'Workers';
                    $this_business_data[$column . '_' . $class] = $object->$method($class);
                }
            }
            $workingPop = 0;
            foreach ($this->populationClasses as $class) {
                foreach ($this->game_businesses as $business) {
                    $workingPop += $business->getCurrentWorkers($class);
                }
                $workingPop += $this->populationPool[$class];
            }


            $this_business_data['population'] = $workingPop;
            $data[] = $this_business_data;
        }

        return $data;
    }

    public function startEngine()
    {
        $this->setupEngine();

        while (true) {
            if (!$this->runPopulationInflux()) {
                break;
            } else {
                $this->round_counter++;
                $this->processRound();
            }
            sleep(self::ROUND_DELAY);
        }
    }
}