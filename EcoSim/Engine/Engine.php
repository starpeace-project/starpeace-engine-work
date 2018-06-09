<?php

namespace EcoSim\Engine;

use EcoSim\Entities\Product;
use EcoSim\Entities\Farm;

class Engine
{

    private $game_products = null;
    private $game_businesses = null;



    private $business_needs = [];

    private $trade_centre = null;

    private $maxPopulationInflux = 25; // Percentage of need;
    private $roundPercentageIncrease = [];

    private $populationNeeded = [];

    private $populationPool = [];

    private $populationClasses = ['low_class', 'middle_class', 'high_class'];

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


        $this->setupTradeCentre();
        $this->setupBusinesses();
        $this->setup();
    }

    public function setup() {
        $this->setupPopulationPools();
        $this->setupPopulationNeeded();
        $this->setupBusinessNeeding();
        $this->setupBusinessNeeds();
    }

    public function reset() {}

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

    public function setupPopulationPools()
    {
        foreach ($this->populationClasses as $class) {
            $this->populationPool[$class] = 0;
        }
    }

    public function resetPopulationPools()
    {
        $this->setupPopulationPools();
    }

    public function setupPopulationNeeded()
    {
        foreach ($this->populationClasses as $class) {
            $this->populationNeeded[$class] = 0;
        }
    }

    public function resetPopulationNeeded()
    {
        $this->setupPopulationNeeded();
    }

    public function setupBusinesses()
    {
        // We will start with two farms
        $this->game_businesses['farm1'] = new Farm();
        $this->game_businesses['farm2'] = new Farm();
    }

    public function setupBusinessNeeding() {
        foreach ($this->populationClasses as $class) {
            $this->business_needs['needing'][$class] = 0;
        }
    }

    public function resetBusinessNeeding() {
        $this->setupBusinessNeeding();
    }

    public function setupBusinessNeeds()
    {
        foreach ($this->game_businesses as $index => $business) {
            foreach ($this->populationClasses as $class) {
                $this->business_needs[$index]['workers'][$class] = 0;
            }
        }
    }

    public function getIndividualBusinessNeeds()
    {
        foreach ($this->game_businesses as $index => $business) {
            foreach ($this->populationClasses as $class) {
                $required = $business->getJobsRequired($class);
                if (!empty($required)) {
                    $this->business_needs[$index]['workers'][$class] = $required;
                    if (empty($this->business_needs['needing'][$class])) {
                        $this->business_needs['needing'][$class] = 1;
                    } else {
                        $this->business_needs['needing'][$class]++;
                    }
                    echo "Business: {$index} needs {$business->getJobsRequired($class)} {$class} workers." . PHP_EOL;
                } else {
                    echo "{$index} does not need {$class} workers." . PHP_EOL;
                }

            }
        }
        foreach ($this->populationClasses as $class) {
            if (!empty($this->business_needs['needing'][$class])) {
                echo "Businesses needing {$class} workers= {$this->business_needs['needing'][$class]}." . PHP_EOL;
            }
        }
    }

    public function setBusinessWorkerNeeds()
    {
        foreach ($this->game_businesses as $index => $business) {
            foreach ($this->populationClasses as $class) {
                if (!empty($this->business_needs[$index]['workers'][$class])) { // If this business needs workers
                    $this->populationNeeded[$class] += $this->business_needs[$index]['workers'][$class];
                    echo "Adding {$this->business_needs[$index]['workers'][$class]} {$class} workers to population need on behalf of {$index}." . PHP_EOL;
                }
            }
            foreach ($this->populationClasses as $class) {
                if (!empty($this->populationNeeded[$class])) {
                    echo "Population need for {$class} now = {$this->populationNeeded[$class]}." . PHP_EOL;
                }
            }
        }
    }

    public function setRoundPercentageIncreases()
    {
        foreach ($this->populationClasses as $class) {
            if (!empty($this->populationNeeded[$class])) {
                $this->roundPercentageIncrease[$class] = mt_rand(10, $this->maxPopulationInflux);
                echo "{$class} will be increased by {$this->roundPercentageIncrease[$class]}% this round." . PHP_EOL;
            }
        }
    }

    private function increasePopulationPool()
    {
        foreach ($this->populationClasses as $class) {
            if (!empty($this->populationNeeded[$class])) {
                if (!empty($this->roundPercentageIncrease[$class])) {
                    $amountToIncrease = ($this->populationNeeded[$class] / 100) * $this->roundPercentageIncrease[$class];
                    $amountToIncrease = round($amountToIncrease, 0, PHP_ROUND_HALF_UP);
                    echo "Amount of population added to pool for {$class} this round is {$amountToIncrease}." . PHP_EOL;
                    if (empty($amountToIncrease)) {
                        if(!empty($this->business_needs['needing'][$class])) {
                            $this->populationPool[$class]++;
                        }
                    }
                    $this->populationPool[$class] += $amountToIncrease;
                    $this->populationIncreased = true;
                }
            }
        }
    }

    private function distributePopulationPool()
    {
        if ($this->populationIncreased) {
            echo "Distributing population pool." . PHP_EOL;
            foreach ($this->game_businesses as $index => $business) { // Each business
                foreach ($this->populationClasses as $class) { // Each population class
                    if (!empty($this->populationPool[$class])) { // Does this class have workers to distribute?
                        echo "{$class} pool has {$this->populationPool[$class]} workers in it." . PHP_EOL;


                        if (!empty($this->business_needs['needing'][$class])) { // Are there any businesses in this class needing workers?


                            if (!empty($this->business_needs[$index]['workers'][$class])) { // Does this specific business need workers



                                echo "{$index} {$class} business needs workers = {$this->business_needs[$index]['workers'][$class]}" . PHP_EOL;
                                echo "{$class} business needs businesses needing workers = {$this->business_needs['needing'][$class]}" . PHP_EOL;
                                if (!empty($this->populationPool[$class] % $this->business_needs['needing'][$class] != 0)) {


                                    echo "Population pool for {$class} divided between the amount of businesses needing them is not even." . PHP_EOL;
                                    echo "so rounding up before adding." . PHP_EOL;
                                    $amountToAdd = round($this->populationPool[$class] / $this->business_needs['needing'][$class],
                                        0, PHP_ROUND_HALF_UP);
                                    // $amountToAdd = min($amountToAdd, $this->business_needs[$index]['workers'][$class]);
                                    echo "Adding {$amountToAdd} {$class} population to {$index}." . PHP_EOL;
                                    echo "{$index} ";
                                    $business->setCurrentWorkers($class, $amountToAdd);
                                    $business->reduceJobsRequired($class, $amountToAdd);
                                    /**
                                     * Ok so we have given this business workers, we need to ensure these workers have been taken off the pile.
                                     * amountToHad is the exact amount to reduce the pool by for this class
                                     */
                                    $this->populationPool[$class] = $this->populationPool[$class] - $amountToAdd; // deliberate long syntax.
                                    /**
                                     * Ok so this business no longer needs workers so we will
                                     */
                                    $this->business_needs['needing'][$class] = $this->business_needs['needing'][$class] - 1;
                                    /**
                                     * So reduce businesses needing workers this round by 1 as this business has its workers this round
                                     */
                                    echo "{$class} population pool now has {$this->populationPool[$class]} workers in it." . PHP_EOL;
                                } else {
                                    $amountToAdd = $this->populationPool[$class] / $this->business_needs['needing'][$class];
                                    $amountToAdd = min($amountToAdd, $this->business_needs[$index]['workers'][$class]);
                                    echo "Adding {$amountToAdd} {$class} population to {$index}" . PHP_EOL;
                                    echo "{$index} ";
                                    $business->setCurrentWorkers($class, $amountToAdd);
                                    $business->reduceJobsRequired($class, $amountToAdd);
                                    /**
                                     * Ok so we have given this business workers, we need to ensure these workers have been taken off the pile.
                                     * amountToHad is the exact amount to reduce the pool by for this class
                                     */
                                    $this->populationPool[$class] = $this->populationPool[$class] - $amountToAdd; // deliberate long syntax.
                                    /**
                                     * Ok so this business no longer needs workers so we will
                                     */
                                    $this->business_needs['needing'][$class] = $this->business_needs['needing'][$class] - 1;
                                    /**
                                     * So reduce businesses needing workers this round by 1 as this business has its workers this round
                                     */
                                    echo "{$class} population pool now has {$this->populationPool[$class]} workers in it." . PHP_EOL;
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->populationIncreased = false;
    }

    public
    function resetBusinessNeeds()
    {
        $this->setupBusinessNeeds();
    }

    public
    function juggleBusinesses()
    {
        $this->game_businesses = array_reverse($this->game_businesses);
    }

    public
    function run()
    {

        for ($i = 1; $i <= 100; $i++) {
            echo "////////////////////ROUND {$i} BEGINS/////////////////////////" . PHP_EOL;
            $this->getIndividualBusinessNeeds();
            $this->setBusinessWorkerNeeds();
            $this->setRoundPercentageIncreases();
            $this->increasePopulationPool();
            $this->distributePopulationPool();
            $this->resetPopulationPools();
            $this->resetPopulationNeeded();
            $this->resetBusinessNeeding();
            $this->resetBusinessNeeds();
            echo "////////////////////ROUND {$i} FINISHED///////////////////////" . PHP_EOL . PHP_EOL;
        }

    }
}