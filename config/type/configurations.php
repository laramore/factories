<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Field factory
    |--------------------------------------------------------------------------
    |
    | This option defines if the fields use factory.
    |
    */

    'char' => [
        'factory_name' => 'word',
    ],
    'integer' => [
        'factory_name' => 'randomNumber',
    ],
    'unsigned_integer' => [
        'factory_name' => 'randomNumber',
    ],
    'big_integer' => [
        'factory_name' => 'randomNumber',
    ],
    'small_integer' => [
        'factory_name' => 'randomNumber',
    ],
    'big_unsigned_integer' => [
        'factory_name' => 'randomNumber',
    ],
    'small_unsigned_integer' => [
        'factory_name' => 'randomNumber',
    ],
    'email' => [
        'factory_name' => 'safeEmail',
    ],
    'primary_id' => [
        'factory_name' => null,
    ],
    'json' => [
        'factory_name' => null,
    ],
    'timestamp' => [
        'factory_name' => null,
    ]

];
