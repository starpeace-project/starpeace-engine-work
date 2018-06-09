<?php

namespace EcoSim\Entities;


class TradeCenter
{
    public $products = [
        [
            'product_name' => 'chemicals',
            'set_price' => 4,
            'set_quantity' => 10,
            'set_measure' => 'ltr',
        ],
        [
            'product_name' => 'seeds',
            'set_price' => 1,
            'set_quantity' => 200,
            'set_measure' => 'each',
        ],
        [
            'product_name' => 'movies',
            'set_price' => 12,
            'set_quantity' => 1,
            'set_measure' => 'film',
        ],
        [
            'product_name' => 'clothes',
            'set_price' => 10,
            'set_quantity' => 1,
            'set_measure' => 'each',
        ],
        [
            'product_name' => 'processed_food',
            'set_price' => 2,
            'set_quantity' => 1,
            'set_measure' => 'each',
        ],
    ];

    public function product_quantity_multiplier() {

    }
}