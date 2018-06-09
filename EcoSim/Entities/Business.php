<?php

namespace EcoSim\Entities;

use EcoSim\Tools\Calculator;

/**
 * Class Business
 * @package EcoSim\Abstracts
 */
class Business extends Building
{
    const DEBUG = true;
    protected $required_products = [];
    protected $output_products = [];
    protected $jobs_required = [];
    protected $current_workers = [];
    protected $required_workers = [];

    protected $wages = [];
    protected $wage_desires = [];
    protected $unemployment_desires = [];
    protected $desires = [];

    public function __construct($name=null, $class=null)
    {
        parent::__construct($name, $class);
    }

    /**
     * @param $class
     * @return int|mixed
     */
    public function getCurrentWorkers($class) {
        if (empty($this->current_workers[$class])) {
            return 0;
        }
        return $this->current_workers[$class];
    }

    /**
     * @param $class
     * @param $value
     */
    public function setCurrentWorkers($class, $value) {
        $this->current_workers[$class] += $value;
    }

    public function reduceJobsRequired($class, $value) {
        $this->jobs_required[$class] -= $value;
    }

    /**
     * @param $class
     * @return int|mixed
     */
    public function getRequiredWorkers($class=null) {
        if (empty($class)) {
            return $this->required_workers;
        } else {
            return $this->required_workers[$class] - $this->current_workers[$class];
        }
    }

    /**
     * @return array
     */
    public function getRequiredProducts()
    {
        return $this->required_products;
    }

    /**
     * @return array
     */
    public function getOutputProducts()
    {
        return $this->output_products;
    }

    /**
     * @param $class
     * @return mixed
     */
    public function getWageLevel($class)
    {
        return $this->wages[$class];
    }

    /**
     * @param $class
     * @param $value
     */
    public function setWageLevel($class, $value)
    {
        $this->wages[$class] = $value;
    }

    /**
     * @param $values
     * @return bool
     */
    public function setRequiredProducts($values)
    {
        $values = getArray($values);

        foreach ($values as $value) {
            if (!in_array($value, $this->required_products)) {
                $this->required_products[] = $value;
            }
        }

        return !empty($values);
    }

    /**
     * @param $values
     * @return bool
     */
    public function setOutputProducts($values)
    {
        $values = getArray($values);

        foreach ($values as $value) {
            if (!in_array($value, $this->output_products)) {
                $this->output_products[] = $value;
            }
        }

        return !empty($values);
    }

    /**
     * @param $values
     * @return bool
     */
    public function setJobsRequired($values)
    {
        if (!is_array($values)) {
            return false;
        }

        foreach ($values as $value) {
            if (!in_array($value, $this->jobs_required)) {
                $this->jobs_required[] = $value;
            }
        }

        return !empty($values);
    }

    public function getWageDesirability($class)
    {
        return $this->wage_desires[$class];
    }

    public function setWageDesirability($value) {
        $this->wage_desires = $value;
    }

    public function getWageDesires() {
        return $this->wage_desires;
    }

    public function getUnemploymentDesire($class)
    {
        return $this->unemployment_desires[$class];
    }

    public function getUnemploymentDesires() {
        return $this->unemployment_desires;
    }

    public function runCalculations($average_wages) {
        foreach ($this->jobs_required as $class => $numbers) {
            $required = $this->jobs_required[$class];
            $current = $this->current_workers[$class];
            echo self::DEBUG ? "Running calculation for {$class}, jobs required= {$numbers}" . PHP_EOL : '';
            echo self::DEBUG ? "Required = {$required} minus Current = {$current} will give us" . PHP_EOL : '';
            $this->unemployment_desires[$class] = Calculator::getPercentage($numbers, $this->jobs_required[$class]);
            echo self::DEBUG ? "{$class} unemployment desires = {$this->unemployment_desires[$class]}" . PHP_EOL : '';
            $this->wage_desires[$class] = Calculator::getPercentage($this->wages[$class], $average_wages[$class]);
            echo self::DEBUG ? "{$class} wage desires = {$this->wage_desires[$class]}" . PHP_EOL : '';
            $this->required_workers[$class] = $this->jobs_required[$class];

            $this->desires[$class] = Calculator::getPercentage($this->wage_desires[$class] + $this->unemployment_desires[$class], 2 * 100);
            echo self::DEBUG ? "Total desire for {$this->getName()} {$this->getClass()}= {$this->desires[$class]}" . PHP_EOL : '';
        }
    }

    public function getDesires()
    {
        return $this->desires;
    }

    public function addWorkers($class, $amount) {
        //need to add to workers and reduce required
        $this->current_workers[$class] += $amount;
        $this->required_workers[$class] -= $amount;
    }

    public function removeWorkers($class, $amount) {
        //need to add to required and reduce workers
        $this->required_workers[$class] += $amount;
        $this->current_workers[$class] -= $amount;
    }
}