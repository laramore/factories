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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\{
    Str, Arr
};
use Laramore\Contracts\Field\RelationField;
use Laramore\Facades\Option;

class Factory extends BaseFactory
{
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
        return $this->getModelClass()::getMeta();
    }

    /**
     * Define a parent relationship for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Factories\Factory  $factory
     * @param  string|null  $relationship
     * @return static
     */
    public function for(BaseFactory $factory, $relationship = null)
    {
        return $this->newInstance([
            'for' => $this->for->merge([
                Str::snake($relationship ?: Str::camel(class_basename($factory->modelName()))) => $factory,
            ]),
        ]);
    }
    
    /**
     * Create the parent relationship resolvers (as deferred Closures).
     *
     * @return array
     */
    protected function parentResolvers()
    {
        $this->for = $this->for->map(function ($factory) {
            if ($factory instanceof BaseFactory) {
                return $factory->create();
            }

            return $factory;
        });

        return $this->for->all();
    }

    /**
     * Define a child relationship for the model.
     *
     * @param  \Illuminate\Database\Eloquent\Factories\Factory  $factory
     * @param  string|null  $relationship
     * @return static
     */
    public function has(BaseFactory $factory, $relationship = null)
    {
        return $this->newInstance([
            'has' => $this->has->merge([
                Str::snake($relationship ?: $this->guessRelationship($factory->modelName())) => $factory,
            ]),
        ]);
    }

    /**
     * Create the children for the given model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function createChildren(Model $model)
    {
        Model::unguarded(function () use ($model) {
            $this->has->each(function ($has, $relationship) use ($model) {
                $field = $this->getMeta()->getField($relationship)->getReversedField();

                $children = $has->state([
                    $field->getName() => $model,
                ])->create();

                $model->setRelationValue($relationship, $children);
            });
        });
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

    /**
     * Proxy dynamic factory methods onto their proper methods.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (! Str::startsWith($method, ['for', 'has'])) {
            static::throwBadMethodCallException($method);
        }

        $relationship = Str::camel(Str::substr($method, 3));

        $relatedModel = get_class($this->newModel()->{$relationship}()->getRelated());

        $factory = $relatedModel::factory();

        if (Str::startsWith($method, 'for')) {
            return $this->for($factory->state($parameters[0] ?? []), $relationship);
        } elseif (Str::startsWith($method, 'has')) {
            return $this->has(
                $factory
                    ->count(is_numeric($parameters[0] ?? null) ? $parameters[0] : 1)
                    ->state((is_callable($parameters[0] ?? null) || is_array($parameters[0] ?? null)) ? $parameters[0] : ($parameters[1] ?? [])),
                $relationship
            );
        }
    }
}
