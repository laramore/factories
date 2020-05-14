<?php
/**
 * Factory facade to generate models.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Factories;

use Illuminate\Database\Eloquent\Factory as BaseFactory;
use Laramore\Contracts\Manager\LaramoreManager;
use Laramore\Traits\IsLocked;

class Factory extends BaseFactory implements LaramoreManager
{
    use IsLocked;

    /**
     * Create a builder for the given model.
     *
     * @param  string|mixed $class
     * @param  string       $name
     * @return \Laramore\Factories\FactoryBuilder
     */
    public function of($class, string $name='default')
    {
        return new FactoryBuilder(
            $class, $name, $this->definitions, $this->states,
            $this->afterMaking, $this->afterCreating, $this->faker
        );
    }

    /**
     * Load factories from path.
     *
     * @param  string $path
     * @return self
     */
    public function load(string $path)
    {
        $this->needsToBeUnlocked();

        return parent::load($path);
    }

    /**
     * Define a class with a given set of attributes.
     *
     * @param  string|mixed $class
     * @param  callable     $attributes
     * @param  string       $name
     * @return self
     */
    public function define($class, callable $attributes, string $name='default')
    {
        $this->needsToBeUnlocked();

        return parent::define($class, $attributes, $name);
    }

    /**
     * Define a state with a given set of attributes.
     *
     * @param  string|mixed   $class
     * @param  string         $state
     * @param  callable|array $attributes
     * @return self
     */
    public function state($class, string $state, string $attributes)
    {
        $this->needsToBeUnlocked();

        return parent::state($class, $state, $attributes);
    }

    /**
     * Create an instance of the given model and persist it to the database.
     *
     * @param  string|mixed $class
     * @param  array        $attributes
     * @param  string       $name
     * @return mixed
     */
    public function create($class, array $attributes=[], string $name=null)
    {
        if (\is_null($name)) {
            return parent::create($class, $attributes);
        }

        return parent::createAs($class, $name, $attributes);
    }

    /**
     * Create an instance of the given model.
     *
     * @param  string|mixed $class
     * @param  array        $attributes
     * @param  string       $name
     * @return mixed
     */
    public function make($class, array $attributes=[], string $name=null)
    {
        if (\is_null($name)) {
            return parent::make($class, $attributes);
        }

        return parent::makeAs($class, $name, $attributes);
    }

    /**
     * Nothing to do to lock.
     *
     * @return void
     */
    public function locking()
    {
    }

    /**
     * Unset the value at the given offset.
     *
     * @param  string|mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->needsToBeUnlocked();

        parent::offsetUnset($offset);
    }
}
