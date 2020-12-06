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
     * @param  string                              $modelName
     * @param  integer|null                        $count
     * @param  \Illuminate\Support\Collection|null $states
     * @param  \Illuminate\Support\Collection|null $has
     * @param  \Illuminate\Support\Collection|null $for
     * @param  \Illuminate\Support\Collection|null $with
     * @param  \Illuminate\Support\Collection|null $afterMaking
     * @param  \Illuminate\Support\Collection|null $afterCreating
     * @param  string|null                         $connection
     * @return void
     */
    public function __construct(string $modelName,
                                $count=null,
                                ?Collection $states=null,
                                ?Collection $has=null,
                                ?Collection $for=null,
                                ?Collection $with=null,
                                ?Collection $afterMaking=null,
                                ?Collection $afterCreating=null,
                                $connection=null)
    {
        $this->model = $modelName;

        parent::__construct($count, $states, $has, $for, $with, $afterMaking, $afterCreating, $connection);
    }

    /**
     * Get a new factory instance for the given attributes.
     *
     * @param  callable|array $attributes
     * @return static
     */
    public static function new($attributes=[])
    {
        return (new static($this->model))->state($attributes)->configure();
    }

    /**
     * Create a new instance of the factory builder with the given mutated properties.
     *
     * @param  array $arguments
     * @return static
     */
    protected function newInstance(array $arguments=[])
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
