<?php
/**
 * Factory mixin for fields.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Mixins;

use Laramore\Factories\Factory;


class FactoryModel
{
    /**
     * Return factory for this model.
     *
     * @return Factory
     */
    public static function factory()
    {
        return function (...$parameters)
        {
            $factory = static::newFactory() ?: Factory::factoryForModel(static::class);
    
            return $factory
                        ->count(is_numeric($parameters[0] ?? null) ? $parameters[0] : null)
                        ->state(is_array($parameters[0] ?? null) ? $parameters[0] : ($parameters[1] ?? []));
        };
    }

    /**
     * Make with factory a new model.
     *
     * @return mixed
     */
    public static function generate()
    {
        return function (...$parameters)
        {
            return static::factory((\array_shift($parameters) ?? null), (\array_shift($parameters) ?? null))
                ->make(...$parameters);
        };
    }

    /**
     * Make with factory a new model.
     *
     * @return mixed
     */
    public static function new()
    {
        return function (...$parameters)
        {
            return static::factory((\array_shift($parameters) ?? null), (\array_shift($parameters) ?? null))
                ->create(...$parameters);
        };
    }
}
