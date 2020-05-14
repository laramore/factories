<?php

use Laramore\Facades\Factory;

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
            'callback' => 'factory',
        ],
        'defineFactory' => [
            'static' => true,
            'callback' => [Factory::class, 'define'],
        ],
        'defineState' => [
            'static' => true,
            'callback' => [Factory::class, 'state'],
        ],
        'conceive' => [
            'static' => true,
            'callback' => [Factory::class, 'create'],
        ],
        'make' => [
            'static' => true,
            'callback' => [Factory::class, 'make'],
        ],
    ],

];
