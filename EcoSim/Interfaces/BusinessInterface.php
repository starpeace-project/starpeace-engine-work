<?php

namespace EcoSim\Interfaces;


interface BusinessInterface
{
    public function getRequiredProducts();
    public function getOutputProducts();
    public function getRequiredWorkers($class);
    public function getCurrentWorkers($class);
    public function getWageLevel($class);

    public function setRequiredProducts($values);
    public function setOutputProducts($values);
    public function setJobsRequired($values);
    public function setWageLevel($class, $value);

    public function __construct();
}