<?php
/**
 * Factory fake generator.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Factories;

use Illuminate\Support\Collection;

class FakeFactory extends Factory
{
    /**
     * Create a new factory instance.
     *
     * @param string $modelName
     * @param  int|null  $count
     * @param  \Illuminate\Support\Collection  $states
     * @param  \Illuminate\Support\Collection  $has
     * @param  \Illuminate\Support\Collection  $for
     * @param  \Illuminate\Support\Collection  $afterMaking
     * @param  \Illuminate\Support\Collection  $afterCreating
     * @param  string  $connection
     * @return void
     */
    public function __construct(string $modelName,
                                $count = null,
                                ?Collection $states = null,
                                ?Collection $has = null,
                                ?Collection $for = null,
                                ?Collection $afterMaking = null,
                                ?Collection $afterCreating = null,
                                $connection = null)
    {
        $this->model = $modelName;

        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection);
    }

    /**
     * Get a new factory instance for the given attributes.
     *
     * @param  callable|array  $attributes
     * @return static
     */
    public static function new($attributes = [])
    {
        return (new static($this->model))->state($attributes)->configure();
    }

    /**
     * Create a new instance of the factory builder with the given mutated properties.
     *
     * @param  array  $arguments
     * @return static
     */
    protected function newInstance(array $arguments = [])
    {
        return new static(...array_values(array_merge([
            'modelName' => $this->model,
            'count' => $this->count,
            'states' => $this->states,
            'has' => $this->has,
            'for' => $this->for,
            'afterMaking' => $this->afterMaking,
            'afterCreating' => $this->afterCreating,
            'connection' => $this->connection,
        ], $arguments)));
    }
}
