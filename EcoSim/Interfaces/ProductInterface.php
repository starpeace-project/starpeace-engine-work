<?php

namespace EcoSim\Interfaces;


interface ProductInterface
{
    public function getName();
    public function getPrice();
    public function getQuality();

    public function setName($value);
    public function setPrice($value);
    public function setQuality($value);
}