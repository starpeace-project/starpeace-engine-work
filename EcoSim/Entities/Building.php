<?php

namespace EcoSim\Entities;

class Building
{
    protected $id;

    /**
     * @var string $name
     */
    protected $name;
    protected $owner;
    protected $class;
    protected $dimensions = [
        'x' => 0,
        'y' => 0,
    ];
    protected $coordinates = [
        'x' => 0,
        'y' => 0,
    ];
    protected $level = 1;
    protected $value = 0;
    protected $images = [
        'construction_level_one' => '',
        'construction_level_two' => '',
        'construction_level_three' => '',
        'construction_level_four' => '',
        'construction_level_five' => '',
        'construction_level_six' => '',
        'construction_level_seven' => '',
        'construction_level_eight' => '',

        'needing_repair_level_one' => '',
        'needing_repair_level_two' => '',
        'needing_repair_level_three' => '',
        'needing_repair_level_four' => '',

        'constructed' => '',

        'for_sale' => '',

        'thumbnail' => '',
    ];
    protected $connected = false;
    protected $pollutionLevel = 0;
    protected $desirability = 0;

    public function __construct($name, $class)
    {
        $this->name = $name;
        $this->class = $class;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($value) {
        $this->connected = $value;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @method setName
     * @param $value
     */
    public function setName($value) {
        $this->name = $value;
    }

    public function getOwner() {
        return $this->owner;
    }

    public function setOwner($value) {
        $this->owner = $value;
    }

    public function getDimensions() {
        return $this->dimensions;
    }

    public function setDimensions($x, $y) {
        $this->dimensions = [
            'x' => $x,
            'y' => $y,
        ];
    }

    public function getCoordinates() {
        return $this->coordinates;
    }

    public function setCoordinates($x, $y) {
        $this->dimensions = [
            'x' => $x,
            'y' => $y,
        ];
    }

    public function getLevel() {
        return $this->level;
    }

    public function setLevel($value) {
        $this->level = $value;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function getImage($key) {
        return $this->images[$key];
    }

    public function setImage($key, $value) {
        $this->images[$key] = $value;
    }

    public function getImages($values) {
        $images = [];
        foreach($values as $key) {
            $images[$key] = $this->images[$key];
        }
        return $images;
    }

    public function setImages($values) {
        $this->images = $values;
    }

    public function getConnected()
    {
        return $this->connected;
    }

    public function setConnected($value)
    {
        $this->connected = $value;
    }

    public function getPollutionLevel()
    {
        return $this->pollutionLevel;
    }

    public function setPollutionLevel($value) {
        $this->pollutionLevel = $value;
    }

    public function getDesirability()
    {
        return $this->desirability;
    }

    public function setDesirability($value) {
        $this->desirability = $value;
    }

    public function increaseDesirability($value) {
        $this->desirability += $value;
    }

    public function decreaseDesirability($value)
    {
        $this->desirability -= $value;
    }

}