<?php

namespace EcoSim\Entities;

use EcoSim\Interfaces\ProductInterface;

class Product implements ProductInterface
{
    protected $name;
    protected $price;
    protected $quality;
    protected $quantity;

    public function __construct($name, $price, $quantity = 1, $quality = 50)
    {
        $this->name = $name;
        $this->price = $price;
        $this->quality = $quality;
        $this->quantity = $quantity;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getQuality()
    {
        return $this->quality;
    }

    public function setName($value)
    {
        if (!is_string($value)) {
            throw new \Exception('Product name must be string');
        }

        return $this->name = $value;
    }

    public function setPrice($value)
    {
        if (!is_float($value)) {
            throw new \Exception('Product price must be float');
        }

        return $this->price = $value;
    }

    public function setQuality($value)
    {
        if (!is_integer($value)) {
            throw new \Exception('Product quality must be integer');
        }

        return $this->quality = $value;
    }
}