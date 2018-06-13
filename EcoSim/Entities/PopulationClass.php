<?php

namespace EcoSim\Entities;


use EcoSim\Tools\Calculator;

class PopulationClass
{
    const DEBUG = false;

    private $name;
    private $pool = 0;
    private $influx = 0;
    private $needed = 0;
    private $average_wage_level = 1;
    private $average_rent = 1;
    private $business_wage_desire = 0;
    private $residential_rent_desire = 0;
    private $residential_occupancy_desire = 0;
    private $unemployment_desire = 0;
    private $unemployment_level = 0;
    private $required_population = 0;

    public function __construct($name=null)
    {
        $this->name = $name;
    }

    public function getClass() {
        return $this->getName();
    }

    public function getName() {
        return $this->name;
    }

    public function setName($value) {
        $this->name = $value;
    }

    public function getPool()
    {
        return $this->pool;
    }

    public function setPool($value)
    {
        echo self::DEBUG ? "Adding {$value} Workers to the {$this->getClass()} pool" . PHP_EOL : '';
        $this->pool += $value;
    }

    public function getInflux()
    {
        return $this->influx;
    }

    public function setInflux($value)
    {
        $this->influx += $value;
    }

    public function getNeeded()
    {
        return $this->needed;
    }

    public function setNeeded($value)
    {
        $this->needed += $value;
    }

    public function getAverageWageLevel() {
        return $this->average_wage_level;
    }

    public function setAverageWageLevel($value) {
        $this->average_wage_level = $value;
    }

    public function getAverageRent()
    {
        return $this->average_rent;
    }

    public function setAverageRent($value) {
        $this->average_rent = $value;
    }

    public function getUnemploymentLevel()
    {
        return $this->unemployment_level;
    }

    public function setUnemploymentLevel($value)
    {
        $this->unemployment_level = $value;
    }

    public function getBusinessWageDesire() {
        return $this->business_wage_desire;
    }

    public function setBusinessWageDesire($value) {
        $this->business_wage_desire = $value;
    }

    public function getResidentialRentDesire() {
        return $this->residential_rent_desire;
    }

    public function setResidentialRentDesire($value)
    {
        $this->residential_rent_desire = $value;
    }

    public function getUnemploymentDesire() {
        return $this->unemployment_desire;
    }

    public function setUnemploymentDesire($value) {
        $this->unemployment_desire = $value;
    }

    public function getResidentialOccupancyDesire()
    {
        return $this->residential_occupancy_desire;
    }

    public function setResidentialOccupancyDesire($value)
    {
        $this->residential_occupancy_desire = $value;
    }

    public function increasePool() {
        echo self::DEBUG ? "Increasing {$this->getClass()} pool" . PHP_EOL : '';
        //This is where we increase the population before distribution occurs
        $total_desires = 4;
        $desire_total =
            $this->business_wage_desire +
            $this->residential_rent_desire +
            $this->residential_occupancy_desire +
            $this->unemployment_desire;
        $overall_desire = Calculator::getPercentage($desire_total, $total_desires * 100);
        $this->setPool(round(Calculator::takePercentageOfWhole($overall_desire, 100),0, PHP_ROUND_HALF_UP));
        echo self::DEBUG ? "Increased {$this->getClass()} pol by {$this->pool} workers." . PHP_EOL : '';
    }

    public function getRequiredPopulation()
    {
        return $this->required_population;
    }

    public function setRequiredPopulation($value) {
        echo self::DEBUG ? "Setting Required Population, adding {$value}" . PHP_EOL : '';
        $this->required_population = $value;
    }

    public function distributePool(&$businesses, &$residentials) {
        // Business first
        /*
         * At a later date, we must reorder business to put in order of desirability
         * this will ensure that the businesses with the highest desire get the population first.
         * Also we should calculate the desires again before this happens to take into account how close the closest
         * residencies with available rooms to rent are, the inverse is also true for residentials, the residential
         * desires should take into account the proximity of work places for its residents, this will continue to ensure fair
         * population dispersal and the more prospering areas will gain more population faster, which imho is realistic.
         */
        $desires = [];
        $desire_total = 0;
        foreach ($businesses as $business) {
            $business_desires = $business->getDesires();
            $desires[][$business->getName()] = $business_desires[$business->getClass()];
            $desire_total += $business_desires[$business->getClass()];
        }
        usort($desires, function ($a, $b) {
            return $a <=> $b;
        });

        $desires = array_reverse($desires);

        // The array is now sorted in desire order
        foreach ($desires as &$business) {
            $name = key($business);
            $business[$name] = round(Calculator::getPercentage($business[$name], $desire_total),0, PHP_ROUND_HALF_DOWN);
        }

        // The desires array now contains businesses and there percentage of this population pool they should take.
        array_pull_flatter($desires);


        foreach ($businesses as $business) {
            $workers = round(Calculator::takePercentageOfWhole($desires[$business->getName()], $this->pool), 0, PHP_ROUND_HALF_DOWN);
            echo self::DEBUG && $this->getClass() == 'middle_class' ? "{$business->getName()} is having {$workers} {$this->getClass()} added." . PHP_EOL : null;
            $business->addWorkers($this->getClass(), $workers);
            $this->pool -= $workers;
        }
    }
}