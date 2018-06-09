<?php

namespace EcoSim\Engine;

use EcoSim\Entities\Product;
use EcoSim\Entities\Farm;
use jc21\CliTable;

class Engine2
{
    const ROUND_DELAY = 0.1;
    const DISPLAY_OUTPUT = true;
    const DISPLAY_COLUMNS = ['current', 'required'];

    private $round_counter = 0;
    private $last_table_colour = '';

    private $game_products = null;
    private $game_businesses = null;

    /**
     * Population Variables
     */
    private $populationClasses = ['low_class', 'middle_class', 'high_class'];
    private $populationPool = [];
    private $populationNeeded = [];

    /**
     * Business Variables
     */
    private $businessCountWorkersNeededByClass = [];

    private $trade_centre = null;

    private $maxPopulationInflux = 25; // Percentage of need;
    private $roundPercentageIncrease = [];

    private $populationIncreased = false;

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
    }

    public function initialisePopulationVariables()
    {
        // Initialise Population pools..
        foreach ($this->populationClasses as $class) {
            $this->populationPool[$class] = 0;
            $this->populationNeeded[$class] = 0;
        }
    }

    public function initialiseBusinessVariables()
    {
        foreach ($this->populationClasses as $class) {
            $this->businessCountWorkersNeededByClass[$class] = 0;
        }
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
        $this->game_businesses['farm2']->setWageLevel('low_class', 5);
        print_r($this->game_businesses['farm2']);exit;
    }

    public function setRoundPopulationPercentageIncreases()
    {
        foreach ($this->populationClasses as $class) {
                $this->roundPercentageIncrease[$class] = mt_rand(10, $this->maxPopulationInflux);
        }
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
                    $this->populationIncreased = true;
                }
            }
        }
    }

    public function distributePopulationPool()
    {
        if ($this->populationIncreased) {
            foreach ($this->game_businesses as $business) {
                foreach ($this->populationClasses as $class) {
                    if (!empty($this->populationPool[$class])) {
                        if (!empty($business->getRequiredWorkers($class))) {
                            if (!empty($this->populationPool[$class] % $this->businessCountWorkersNeededByClass[$class] != 0)) {
                                $amountToAdd = round($this->populationPool[$class] / $this->businessCountWorkersNeededByClass[$class],
                                    0, PHP_ROUND_HALF_UP);
                                $business->setCurrentWorkers($class, $amountToAdd);
                                $business->reduceJobsRequired($class, $amountToAdd);
                                $this->populationPool[$class] -= $amountToAdd; // deliberate long syntax.
                                $this->businessCountWorkersNeededByClass[$class]--;
                            } else {
                                $amountToAdd = $this->populationPool[$class] / $this->businessCountWorkersNeededByClass[$class];
                                $amountToAdd = min($amountToAdd, $business->getRequiredWorkers($class));
                                $business->setCurrentWorkers($class, $amountToAdd);
                                $business->reduceJobsRequired($class, $amountToAdd);
                                $this->populationPool[$class] -= $amountToAdd;
                                $this->businessCountWorkersNeededByClass[$class]--;
                            }
                        }
                    }
                }
            }
        }
    }


    public function processRound()
    {
        $this->resetEngineVariables();
        if ($this->runPopulationInflux()) {
            $this->setRoundPopulationPercentageIncreases();
            $this->setPopulationNeeded();
            $this->processPopulationNeeded();
            $this->distributePopulationPool();
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
        foreach ($this->populationClasses as $class) {
            foreach (self::DISPLAY_COLUMNS as $column) {
                $abv_parts = explode('_', $class);
                foreach ($abv_parts as &$abv_part) {
                    $abv_part = ucfirst($abv_part);
                }
                $abv = implode(' ', $abv_parts);
                $table->addField(ucfirst($column) . ' ' . $abv, $column . '_' . $class, false, 'white');
            }
        }
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
                if (self::DISPLAY_OUTPUT) {
                    $this->outputBusinessPopulationTable($this->getBusinessPopulationData());
                }
            }
            sleep(self::ROUND_DELAY);
        }
    }
}