<?php

namespace Laramore\Fields;

return [

    /*
    |--------------------------------------------------------------------------
    | Default factory formaters
    |--------------------------------------------------------------------------
    |
    | This option defines the default factory formaters.
    | Based on https://github.com/fzaninotto/Faker#formatters .
    |
    */

    Binary::class => [
        'formater' => 'randomNumber',
    ],
    Boolean::class => [
        'formater' => 'boolean',
    ],
    Char::class => [
        'formater' => 'sentence',
    ],
    DateTime::class => [
        'formater' => 'dateTime',
    ],
    Decimal::class => [
        'formater' => 'randomFloat',
    ],
    Email::class => [
        'formater' => 'safeEmail',
    ],
    Enum::class => [
        'formater' => 'randomElement',
    ],
    Increment::class => [
        'formater' => 'randomNumber',
    ],
    Integer::class => [
        'formater' => 'randomNumber',
    ],
    Json::class => [
        'formater' => 'words',
    ],
    ManyToMany::class => [
        'formater' => 'relation',
        'parameters' => [
            'count' => 5,
        ],
    ],
    ManyToOne::class => [
        'formater' => 'relation',
    ],
    OneToOne::class => [
        'formater' => 'relation',
    ],
    Password::class => [
        'formater' => 'password',
        'password' => 'password', // Password used by factory.
    ],
    PrimaryId::class => [
        'formater' => null,
    ],
    Reversed\BelongsToMany::class => [
        'formater' => 'relation',
        'parameters' => [
            'count' => 5,
        ],
    ],
    Reversed\HasMany::class => [
        'formater' => 'relation',
        'parameters' => [
            'count' => 5,
        ],
    ],
    Text::class => [
        'formater' => 'text',
    ],
    Timestamp::class => [
        'formater' => 'unixTime',
    ],
    UniqueId::class => [
        'formater' => null,
    ],
    Uri::class => [
        'formater' => 'url',
    ],
    
];