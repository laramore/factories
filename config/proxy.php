<?php

use Laramore\Factories\Factory;

return [

    /*
    |--------------------------------------------------------------------------
    | Added proxies
    |--------------------------------------------------------------------------
    |
    | These options define all proxy configurations for factories.
    |
    */

    'configurations' => [
        'factory' => [
            'static' => true,
            'callback' => [Factory::class, 'factoryForModel'],
        ],
        'new' => [
            'static' => true,
            'callback' => [Factory::class, 'createForModel'],
        ],
        'make' => [
            'static' => true,
            'callback' => [Factory::class, 'makeForModel'],
        ],
    ],

];
