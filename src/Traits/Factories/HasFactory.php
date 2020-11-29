<?php
/**
 * Factory to generate models.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Illuminate\Database\Eloquent\Factories;

use Illuminate\Database\Eloquent\Factories\HasFactory as BaseHasFactory;
use Laramore\Factories\Factory;


trait HasFactory
{
    use BaseHasFactory;

    /**
     * Get a new factory instance for the model.
     *
     * @param  mixed  $parameters
     * @return \Laramore\Factories\Factory
     */
    public static function factory(...$parameters)
    {
        $factory = static::newFactory() ?: Factory::factoryForModel(get_called_class());

        return $factory
                    ->count(is_numeric($parameters[0] ?? null) ? $parameters[0] : null)
                    ->state(is_array($parameters[0] ?? null) ? $parameters[0] : ($parameters[1] ?? []));
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Laramore\Factories\Factory
     */
    protected static function newFactory()
    {
        //
    }
}
