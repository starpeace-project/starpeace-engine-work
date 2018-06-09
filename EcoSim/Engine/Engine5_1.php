<?php

namespace EcoSim\Engine;

use EcoSim\Entities\Business;
use EcoSim\Entities\PopulationClass;
use EcoSim\Tools\Calculator;
use EcoSim\Entities\Residential;
use EcoSim\Entities\Farm;
use EcoSim\Tools\DisplayTables;

class Engine5_1
{
    const DEBUG = true;

    const ROUND_DELAY = .9;
    const POPULATION_TICK = 1;

    const DISPLAY_OUTPUT = true;

    private $round_counter = 0;
    private $tick_counter = 1;

    private $businesses = null;
    private $residentials = null;

    /**
     * Population Variables
     */
    private $population_classes = ['low_class', 'middle_class', 'high_class'];
    private $population = [];

    /**
     * Business Variables
     */
    private $trade_centre = null;

    public function __construct()
    {
        $this->display_tables = new DisplayTables();
    }

    public function setupEngine()
    {
        $this->setupTradeCentre();
        $this->initialisePopulationTypes();
        $this->setupBusinesses();
        $this->setupResidentials();
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
        $farm1 = new Farm('Rons Farm', 'low_class');
        $this->businesses[] = $farm1;

        $farm2 = new Farm('Bills Farm', 'low_class');
        $farm2->setWageLevel('low_class', 3);
        $this->businesses[] = $farm2;
    }

    public function setupResidentials()
    {
        $ronsApartments = new Residential('Rons Apartments', 'low_class', 30);
        $billsApartments = new Residential('Bills Apartments', 'low_class', 40);

        $this->residentials[] = $ronsApartments;
        $this->residentials[] = $billsApartments;
    }

    public function initialisePopulationTypes()
    {
        foreach ($this->population_classes as $class) {
            $this->population[] = new PopulationClass($class);
        }
    }

    public function getAverageRents()
    {
        $average_rents = [];
        foreach ($this->population as $class) {
            $average_rents[$class->getClass()] = $class->getAverageRent();
        }
        return $average_rents;
    }

    public function getWageAverages()
    {
        $average_wages = [];
        foreach ($this->population as $class) {
            $average_wages[$class->getClass()] = $class->getAverageWageLevel();
        }
        return $average_wages;
    }

    public function calculateBusinessDesires()
    {
        $business_wage_desire_totals = [];
        $unemployment_desire_totals = [];

        foreach ($this->businesses as $business) {
            $business->runCalculations($this->getWageAverages());
            $wage_desires = $business->getWageDesires();
            $unemployment_desires = $business->getUnemploymentDesires();
            foreach ($wage_desires as $desire_class => $desire) {
                $unemployment_desire_totals[$desire_class][] = $desire;
                $business_wage_desire_totals[$desire_class][] = $unemployment_desires[$desire_class];
            }
        }

        foreach ($this->population as $class) {
            $business_wage_desire_total = 0;
            foreach ($business_wage_desire_totals[$class->getClass()] as $total) {
                $business_wage_desire_total += $total;
            }
            $business_unemployment_desire_total = 0;
            foreach ($unemployment_desire_totals[$class->getClass()] as $total) {
                $business_unemployment_desire_total += $total;
            }

            $class->setUnemploymentDesire(Calculator::getPercentage($business_unemployment_desire_total, count($unemployment_desire_totals[$class->getClass()]) * 100));
            $class->setBusinessWageDesire(Calculator::getPercentage($business_wage_desire_total, count($business_wage_desire_totals[$class->getClass()]) * 100));
        }
    }

    public function calculateResidentialDesires()
    {
        $residential_rent_desire_totals = [];
        $residential_occupancy_desire_totals = [];
        foreach ($this->population as $class) {
            foreach ($this->residentials as $residential) {
                $residential->runCalculations($this->getAverageRents());
                if ($residential->getClass() == $class->getClass()) {
                    $residential_rent_desire_totals[] = $residential->getRentDesirability();
                    $residential_occupancy_desire_totals[] = $residential->getOccupancyDesire();
                }
            }
            $class->setResidentialRentDesire(Calculator::getArrayAverage($residential_rent_desire_totals));
            $class->setResidentialOccupancyDesire(Calculator::getArrayAverage($residential_occupancy_desire_totals));
        }
    }


    /**
     * Runs the calculations to set each entities desirabilities
     */
    public function processDesirabilities()
    {
        $this->calculateBusinessDesires();
        $this->calculateResidentialDesires();
    }

    public function increasePopulation()
    {
        $required_populations = [];
        foreach ($this->businesses as $business) {
            $required_workers = $business->getRequiredWorkers();
            foreach ($required_workers as $class => $value) {
                if (!empty($required_populations[$class])) {
                    $required_populations[$class] += $value;
                } else {
                    $required_populations[$class] = $value;
                }
            }
        }

        foreach ($required_populations as $class => $required_population) {
            echo "Class = {$class}" . PHP_EOL;
            foreach ($this->population as $population) {
                echo self::DEBUG ? "Current class = {$population->getClass()}" . PHP_EOL : '';
                if ($population->getClass() == $class) {
                    echo "Current Population Required = {$population->getRequiredPopulation()}" . PHP_EOL;
                    echo "Adding Population Required += {$required_populations[$population->getClass()]}" . PHP_EOL;
                    echo "Classes (class) = {$class}, (population->getClass()) = {$population->getClass()}" . PHP_EOL;
                    $population->setRequiredPopulation($required_populations[$population->getClass()]);
                    $population->increasePool();
                }
            }
        }
    }

    public function distributePopulationPool()
    {
        foreach ($this->population as $population) {
            $population->distributePool($this->businesses, $this->residentials);
        }
    }


    public function processRound($counter)
    {
        $this->processDesirabilities();
        if ($this->tick_counter == self::POPULATION_TICK || TRUE) {
            $this->increasePopulation();
            $this->distributePopulationPool();
            if (self::DISPLAY_OUTPUT) {
                $this->display_tables->displayTable($counter, $this->population, 'business', $this->businesses);
                exit;
               // $this->display_tables->displayTable($this->population, 'residential', $this->residentials);
            }
        }

        if ($this->tick_counter == self::POPULATION_TICK) {
            $this->tick_counter = 1;
        } else {
            $this->tick_counter++;
        }
    }

    public function startEngine()
    {
        $this->setupEngine();

        while (true) {
            $this->round_counter++;
            $this->processRound($this->round_counter);
            sleep(self::ROUND_DELAY);
        }
    }
}