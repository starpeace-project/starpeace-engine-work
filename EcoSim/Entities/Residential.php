<?php

namespace EcoSim\Entities;


use EcoSim\Tools\ArrayManipulators;
use EcoSim\Tools\Calculator;

/**
 * Class Residential
 * @package EcoSim\Entities
 */
class Residential extends Building
{
    /**
     * @var array
     */
    protected $environmentFactors = [];
    /**
     * @var array
     */
    protected $beautyFactors = [];
    /**
     * @var array
     */
    protected $crimeFactors = [];
    /**
     * @var array
     */
    protected $pollutionFactors = [];

    /**
     * @var int
     */
    protected $populationLimit;
    /**
     * @var int
     */
    protected $population = 0;
    /**
     * @var int
     */
    protected $quality = 0;
    /**
     * @var
     */
    protected $rent = 1;
    /**
     * @var
     */
    protected $rent_desirability;
    protected $occupancy_desire = 0;

    /**
     * Residential constructor.
     * @param null $name
     * @param null $class
     * @param null $population_limit
     */
    public function __construct($name=null, $class=null, $population_limit=null)
    {
        $this->populationLimit = $population_limit;
        parent::__construct($name, $class);
    }

    public function getClass() {
        return $this->class;
    }

    /**
     * @return int
     */
    public function getPopulationLimit() {
        return $this->populationLimit;
    }

    /**
     * @param $value
     */
    public function setPopulationLimit($value) {
        $this->populationLimit = $value;
    }

    /**
     * @return int
     */
    public function getPopulation() {
        return $this->population;
    }

    /**
     * @param $value
     */
    public function setPopulation($value) {
        $this->population += $value;
    }

    /**
     * @return int
     */
    public function getQuality() {
        return $this->quality;
    }

    /**
     * @param $value
     */
    public function setQuality($value) {
        $this->quality = $value;
    }

    /**
     * @return array
     */
    public function getEnvironmentFactors() {
        return $this->environmentFactors;
    }

    /**
     * @param $values
     * @param bool $wipe
     */
    public function setEnvironmentFactors($values, $wipe=false) {
        $this->environmentFactors = ArrayManipulators::setArrayValues($this->environmentFactors, $values, $wipe);
    }

    /**
     * @return array
     */
    public function getBeautyFactors() {
        return $this->beautyFactors;
    }

    /**
     * @param $values
     * @param bool $wipe
     */
    public function setBeautyFactors($values, $wipe=false)
    {
        $this->beautyFactors = ArrayManipulators::setArrayValues($this->beautyFactors, $values, $wipe);
    }

    /**
     * @return array
     */
    public function getCrimeFactors()
    {
        return $this->crimeFactors;
    }

    /**
     * @param $values
     * @param bool $wipe
     */
    public function setCrimeFactors($values, $wipe=false) {
        $this->crimeFactors = ArrayManipulators::setArrayValues($this->crimeFactors, $values, $wipe);
    }

    /**
     * @return array
     */
    public function getPollutionFactors()
    {
        return $this->pollutionFactors;
    }

    /**
     * @param $values
     * @param bool $wipe
     */
    public function setPollutionFactors($values, $wipe=false) {
        $this->pollutionFactors = ArrayManipulators::setArrayValues($this->pollutionFactors, $values, $wipe);
    }

    /**
     * @return mixed
     */
    public function getRent()
    {
        return $this->rent;
    }

    /**
     * @param $value
     */
    public function setRent($value)
    {
        $this->rent = $value;
    }

    /**
     * @return mixed
     */
    public function getRentDesirability()
    {
        return $this->rent_desirability;
    }

    /**
     * @param $value
     */
    public function setRentDesirability($value)
    {
        $this->rent_desirability = $value;
    }

    public function calculateRentDesire($average_rents) {
        $this->rent_desirability = Calculator::getPercentage($this->rent, $average_rents[$this->class]);
    }

    public function calculateOccupancyDesire() {
        $this->occupancy_desire = Calculator::getPercentage($this->populationLimit - $this->population, $this->populationLimit);
    }

    public function runCalculations($average_rents) {
        $this->calculateRentDesire($average_rents);
        $this->calculateOccupancyDesire();
    }

    public function getOccupancyDesire() {
        return $this->occupancy_desire;
    }
}