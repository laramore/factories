<?php
/**
 * Factory generator.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Factories;

use Illuminate\Database\Eloquent\Factories\Factory as BaseFactory;

use Illuminate\Support\{
    Collection, Arr
};
use Laramore\Contracts\Field\RelationField;
use Laramore\Facades\Option;


class Factory extends BaseFactory
{
    /**
     * Create a new factory instance.
     *
     * @param  int|null  $count
     * @param  \Illuminate\Support\Collection  $states
     * @param  \Illuminate\Support\Collection  $has
     * @param  \Illuminate\Support\Collection  $for
     * @param  \Illuminate\Support\Collection  $afterMaking
     * @param  \Illuminate\Support\Collection  $afterCreating
     * @param  string  $connection
     * @return void
     */
    public function __construct($count = null,
                                ?Collection $states = null,
                                ?Collection $has = null,
                                ?Collection $for = null,
                                ?Collection $afterMaking = null,
                                ?Collection $afterCreating = null,
                                $connection = null)
    {
        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection);

        $this->meta = $this->getModelClass()::getMeta();
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [];
    }

    /**
     * Return model class name.
     *
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelName();
    }

    /**
     * Return meta associated to model.
     *
     * @return LaramoreMeta
     */
    public function getMeta()
    {
        return $this->meta;
    }
    
    /**
     * Expand all attributes to their underlying values.
     *
     * @param  array  $definition
     * @return array
     */
    protected function expandAttributes(array $definition)
    {
        $definition = $this->generateMissingAttributes($definition);
    
        return collect($definition)->map(function ($attribute, $key) use (&$definition) {
            if (is_callable($attribute) && ! is_string($attribute) && ! is_array($attribute)) {
                $attribute = $attribute($definition);
            }

            if ($attribute instanceof BaseFactory) {
                $attribute = $attribute->create();
            }

            $definition[$key] = $attribute;

            return $attribute;
        })->all();
    }

    /**
     * Generate missing attributes.
     *
     * @param  array  $definition
     * @return array
     */
    protected function generateMissingAttributes(array $definition)
    {
        foreach ($this->getMeta()->getFields() as $field) {
            $name = $field->getName();

            // Relations are handled by states. Moreover, they are auto generated
            // if the relation field has the option required.
            if (($field instanceof RelationField && !$field->hasOption(Option::required()))
                || $field->getOwner() !== $field->getMeta()
                || \is_null($field->getType()->getFactoryName())   
            ) {
                continue;
            }

            if (!Arr::exists($definition, $name)) {
                $definition[$name] = $field->getOwner()->generateFieldValue($field);
            }
        }

        return $definition;
    }

    /**
     * Get a new factory instance for the given model name.
     *
     * @param  string  $modelName
     * @return static
     */
    public static function factoryForModel(string $modelName)
    {
        $factory = static::resolveFactoryName($modelName);

        if (\class_exists($factory)) {
            return $factory::new();
        }

        return new FakeFactory($modelName);
    }

    /**
     * Create with a factory.
     *
     * @param  string  $modelName
     * @return mixed
     */
    public static function createForModel(string $modelName)
    {
        return $modelName::factory()->create();
    }

    /**
     * Make with a factory.
     *
     * @param  string  $modelName
     * @return mixed
     */
    public static function makeForModel(string $modelName)
    {
        return $modelName::factory()->make();
    }
}
