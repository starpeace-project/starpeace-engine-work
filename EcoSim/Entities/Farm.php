<?php

namespace EcoSim\Entities;

/**
 * Class Farm
 * @package EcoSim\Entities
 */
class Farm extends Business
{
    public $required_products = [
            'chemicals' => 40,
            'seeds' => 4000,
    ];

    public $output_products = [
        [
            'product_name' => 'fresh_food',
            'production_quantity'=> 2000,
            'price' => 1,
            'quality' => 0,
        ],
        [
            'product_name' => 'organic_materials',
            'production_quantity' => 200,
            'price' => 3,
            'quality' => 0,
        ],
    ];

    protected $jobs_required = [
            'low_class' => 100,
            'middle_class' => 10,
            'high_class' => 1,
    ];

    public $wages = [
            'low_class' => 1,
            'middle_class' => 2,
            'high_class' => 5,
    ];

    public $current_workers = [
        'low_class' => 0,
        'middle_class' => 0,
        'high_class' => 0,
    ];

    public function getClass() {
        return $this->class;
    }
}