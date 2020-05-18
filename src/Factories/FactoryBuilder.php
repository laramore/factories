<?php
/**
 * Build with a single config instances.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Factories;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\FactoryBuilder as BaseFactoryBuilder;
use Laramore\Contracts\Field\{
    RelationField, ManyRelationField
};
use Laramore\Facades\Factory;

class FactoryBuilder extends BaseFactoryBuilder
{
    /**
     * Generated attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Amount to generate for many relation states.
     *
     * @var int
     */
    protected $stateAmount;

    /**
     * Indicate if the builder has an attribute.
     *
     * @param string $key
     * @return boolean
     */
    public function hasAttribute(string $key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Define an attribute.
     *
     * @param string $key
     * @param mixed  $value
     * @return self
     */
    public function setAttribute(string $key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Return or generate an attribute.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        if ($this->hasAttribute($key)) {
            return $this->attributes[$key];
        }

        if ($this->class::getMeta()->hasField($key)) {
            $field = $this->class::getMeta()->getField($key);

            $this->setAttribute($field->getName(), $value = $field->getOwner()->generateFieldValue($field));

            return $value;
        }
    }

    /**
     * Return all generated attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Merge attributes to generated ones.
     *
     * @param array $attributes
     * @return self
     */
    public function mergeAttributes(array $attributes)
    {
        $this->attributes = \array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * Create a collection of models and persist them to the database.
     *
     * @param  array $attributes
     * @return mixed
     */
    public function create(array $attributes=[])
    {
        $this->creation = true;

        $result = parent::create($attributes);

        $this->creation = false;

        return $result;
    }

    /**
     * Create a collection of models.
     *
     * @param  array $attributes
     * @return mixed
     */
    public function make(array $attributes=[])
    {
        if ($this->amount === null) {
            return tap($this->makeInstance($attributes), function ($instance) {
                $this->callAfterMaking(collect([$instance]));
            });
        }

        if ($this->amount < 1) {
            return (new $this->class)->newCollection();
        }

        $instances = (new $this->class)->newCollection(array_map(function () use ($attributes) {
            return $this->makeInstance($attributes);
        }, range(1, $this->amount)));

        $this->callAfterMaking($instances);

        return $instances;
    }

    /**
     * Create an array of raw attribute arrays.
     *
     * @param  array $attributes
     * @return mixed
     */
    public function raw(array $attributes=[])
    {
        if ($this->amount === null) {
            return $this->getRawAttributes($attributes);
        }

        if ($this->amount < 1) {
            return [];
        }

        return array_map(function () use ($attributes) {
            return $this->getRawAttributes($attributes);
        }, range(1, $this->amount));
    }

    /**
     * Get a raw attributes array for the model.
     *
     * @param  array $attributes
     * @return mixed
     */
    protected function getRawAttributes(array $attributes=[])
    {
        $clone = clone $this;
        $clone->mergeAttributes($attributes);

        return $clone->generateAttributes();
    }

    /**
     * Generate attributes based on attributes, active states and default generation.
     *
     * @return array
     * @throws \InvalidArgumentException If no factory exists with the given name.
     */
    public function generateAttributes()
    {
        if (isset($this->definitions[$this->class][$this->name])) {
            \call_user_func(
                $this->definitions[$this->class][$this->name],
                $this, $this->faker
            );
        } else if ($this->name !== 'default') {
            throw new InvalidArgumentException("Unable to locate factory with name [{$this->name}] [{$this->class}].");
        }

        $this->applyStates($this->activeStates);
        $this->generateMissingAttributes();
        $this->expandAttributes();

        return $this->attributes;
    }

    /**
     * Apply the active states to the model definition array.
     *
     * @param  string $state
     * @param  array  $attributes
     * @return mixed
     */
    public function applyState(string $state, array $attributes=[])
    {
        return $this->applyStates([$state], $attributes);
    }

    /**
     * Apply the active states to the model definition array.
     *
     * @param  array $states
     * @param  array $attributes
     * @return mixed
     */
    public function applyStates(array $states=[], array $attributes=[])
    {
        foreach ($states as $state) {
            if (strpos($state, ':') !== false) {
                [$state, $this->stateAmount] = explode(':', $state);
            }

            if (!isset($this->states[$this->class][$state])) {
                if ($this->stateHasAfterCallback($state)) {
                    continue;
                }

                if (!$this->class::getMeta()->hasField($state, RelationField::class)) {
                    throw new InvalidArgumentException("Unable to locate [{$state}] state for [{$this->class}].");
                }

                $field = $this->class::getMeta()->getField($state, RelationField::class);
                $factory = Factory::of($field->getTargetModel());

                if ($field instanceof ManyRelationField) {
                    $factory->times($this->stateAmount ?? $this->faker->numberBetween(0, 5));
                }

                $this->setAttribute($field->getName(), $factory);
            } else {
                $this->stateAttributes($state, $attributes);
            }

            $this->stateAmount = null;
        }

        return $this;
    }

    /**
     * Get the state attributes.
     *
     * @param  string|mixed $state
     * @param  array        $attributes
     * @return self
     */
    protected function stateAttributes($state, array $attributes=[])
    {
        $this->mergeAttributes($attributes);

        $stateAttributes = $this->states[$this->class][$state];

        if (!\is_callable($stateAttributes)) {
            $this->mergeAttributes($stateAttributes);
        } else {
            \call_user_func(
                $stateAttributes,
                $this, $this->faker,
            );
        }

        return $this;
    }

    /**
     * Generate missing attributes.
     *
     * @return self
     */
    protected function generateMissingAttributes()
    {
        foreach ($this->class::getMeta()->getFields() as $field) {
            // Relations are handled by states.
            if ($field instanceof RelationField
                || $field->getOwner() !== $field->getMeta()
                || \is_null($field->getType()->getFactoryName())) {
                continue;
            }

            $name = $field->getName();

            if (!isset($this->attributes[$name])) {
                $this->setAttribute($name, $field->getOwner()->generateFieldValue($field));
            }
        }

        return $this;
    }

    /**
     * Expand all attributes to their underlying values.
     *
     * @param  array $attributes
     * @return self
     */
    protected function expandAttributes(array $attributes=[])
    {
        $this->mergeAttributes($attributes);

        foreach ($this->attributes as &$attribute) {
            if (is_callable($attribute) && !is_string($attribute) && !is_array($attribute)) {
                $attribute = $attribute($this, $this->faker);
            }

            if ($attribute instanceof static) {
                if ($this->creation) {
                    $attribute = $attribute->create();
                } else {
                    $attribute = $attribute->make();
                }
            }
        }

        return $this;
    }

    /**
     * Check if has attribute.
     *
     * @param string $key
     * @return boolean
     */
    public function __isset(string $key)
    {
        return $this->hasAttribute($key);
    }

    /**
     * Return or generate an attribute.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Set an attribute.
     *
     * @param string $key
     * @param mixed  $value
     * @return mixed
     */
    public function __set(string $key, $value)
    {
        return $this->setAttribute($key, $value);
    }
}
