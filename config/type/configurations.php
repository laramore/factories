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

    'binary' => [
        'factory_name' => 'randomNumber',
    ],
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
    'decimal' => [
        'factory_name' => 'randomFloat',
    ],
    'unsigned_decimal' => [
        'factory_name' => 'randomFloat',
    ],
    'big_decimal' => [
        'factory_name' => 'randomFloat',
    ],
    'small_decimal' => [
        'factory_name' => 'randomFloat',
    ],
    'big_unsigned_decimal' => [
        'factory_name' => 'randomFloat',
    ],
    'small_unsigned_decimal' => [
        'factory_name' => 'randomFloat',
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
