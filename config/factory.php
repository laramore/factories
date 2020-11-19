<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default validation manager
    |--------------------------------------------------------------------------
    |
    | This option defines the manager to handle validations.
    |
    */

    'manager' => Laramore\Factories\FactoryManager::class,

    /*
    |--------------------------------------------------------------------------
    | Name for the defined validation class in options.
    |--------------------------------------------------------------------------
    |
    | This option defines the key name to use to resolve the validation class
    | specific to a option.
    |
    */

    'property_name' => 'factory_name',
    
    'parameters_name' => 'factory_parameters',

    'configurations' => [

    ],

];
