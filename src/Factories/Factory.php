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
    Str, Arr, Collection
};
use Laramore\Contracts\Field\ComposedField;
use Laramore\Contracts\Field\Field;
use Laramore\Contracts\Field\ManyRelationField;
use Laramore\Contracts\Field\RelationField;
use Laramore\Elements\Element;
use Laramore\Facades\Option;

class Factory extends BaseFactory
{
    /**
     * Fields to generate dynamically.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $with;

    /**
     * Create a new factory instance.
     *
     * @param  integer|null                        $count
     * @param  \Illuminate\Support\Collection|null $states
     * @param  \Illuminate\Support\Collection|null $has
     * @param  \Illuminate\Support\Collection|null $for
     * @param  \Illuminate\Support\Collection|null $with
     * @param  \Illuminate\Support\Collection|null $afterMaking
     * @param  \Illuminate\Support\Collection|null $afterCreating
     * @param  string|mixed                        $connection
     * @return void
     */
    public function __construct($count=null,
                                ?Collection $states=null,
                                ?Collection $has=null,
                                ?Collection $for=null,
                                ?Collection $with=null,
                                ?Collection $afterMaking=null,
                                ?Collection $afterCreating=null,
                                $connection=null)
    {
        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection);

        $this->with = $with ?: $this->resolveWith();
    }

    /**
     * Resolve default fields to generate.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function resolveWith()
    {
        $with = new Collection($this->getMeta()->getFields());

        return $with->filter(function (Field $field) {
            return (!$field->hasOption(Option::nullable())
                && !$field->hasDefault()
                && (!\is_null($field->getFactoryFormater()) || \method_exists($field, 'generate'))
                && (!($field instanceof RelationField) || $field->hasOption(Option::required()))
                && ($field->getOwner() === $field->getMeta()
                    || (($field instanceof RelationField) && !$field->isRelationHeadOn())
                )
            );
        });
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
            'count' => $this->count,
            'states' => $this->states,
            'has' => $this->has,
            'for' => $this->for,
            'with' => $this->with,
            'afterMaking' => $this->afterMaking,
            'afterCreating' => $this->afterCreating,
            'connection' => $this->connection,
        ], $arguments)));
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
        return $this->getModelClass()::getMeta();
    }

    /**
     * Add a new "field" to generate.
     *
     * @param  string $fieldName
     * @return static
     */
    public function with(string $fieldName)
    {
        return $this->newInstance(['with' => $this->with->merge([$fieldName => $this->getMeta()->getField($fieldName)])]);
    }

    /**
     * Remove a "field" to generate.
     *
     * @param  string $fieldName
     * @return static
     */
    public function without(string $fieldName)
    {
        return $this->newInstance(['with' => $this->with->diff([$fieldName])]);
    }

    /**
     * Define a parent relationship for the model.
     *
     * @param  BaseFactory|mixed $factory
     * @param  string|null $relationship
     * @return static
     */
    public function for($factory, $relationship=null)
    {
        $relationship = Str::snake($relationship ?: Str::camel(class_basename($factory->modelName())));

        if ($this->getMeta()->hasField($relationship, ManyRelationField::class)) {
            throw new \LogicException("Only use `has` for single relations. Use `has` instead for `$relationship` relationship");
        }

        return $this->newInstance([
            'for' => $this->for->merge([
                $relationship => $factory,
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
     * @param  BaseFactory $factory
     * @param  string|null $relationship
     * @return static
     */
    public function has(BaseFactory $factory, $relationship=null)
    {
        $relationship = Str::snake($relationship ?: $this->guessRelationship($factory->modelName()));

        if ($this->getMeta()->hasField($relationship, RelationField::class) && !$this->getMeta()->hasField($relationship, ManyRelationField::class)) {
            throw new \LogicException("Only use `has` for many relations. Use `for` instead for `$relationship` relationship");
        }

        return $this->newInstance([
            'has' => $this->has->merge([
                $relationship => $factory,
            ]),
        ]);
    }

    /**
     * Create the children for the given model.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    protected function createChildren(Model $model)
    {
        Model::unguarded(function () use ($model) {
            $this->has->each(function ($has, $relationship) use ($model) {
                $field = $this->getMeta()->getField($relationship)->getReversedField();
                $value = $field instanceof ManyRelationField ? $model->newCollection([$model]) : $model;

                if ($has instanceof BaseFactory) {
                    $has = $has->state([
                        $field->getName() => $value,
                    ])->create();
                }

                $model->setRelationValue($relationship, $has);
            });
        });
    }

    /**
     * Expand all attributes to their underlying values.
     *
     * @param  array $definition
     * @return array
     */
    protected function expandAttributes(array $definition)
    {
        $definition = $this->generateMissingAttributes($definition);

        return collect($definition)->map(function ($attribute, $key) use (&$definition) {
            if (is_callable($attribute)
                && !is_string($attribute)
                && !is_array($attribute)
                && !($attribute instanceof Element)) {

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
     * @param  array $definition
     * @return array
     */
    protected function generateMissingAttributes(array $definition)
    {
        $fields = $this->with->toArray();
        $names = array_keys($fields);

        while ($name = array_shift($names)) {
            $field = $fields[$name];

            if (! Arr::exists($definition, $name) && ! ($field instanceof \Laramore\Contracts\Field\LinkField)) {
                if ($field instanceof ManyRelationField && !$this->has->has($name)) {
                    $this->has = $this->has->merge([
                        $name => $field->getOwner()->generateFieldValue($field),
                    ]);
                } else if ($field->hasOption(Option::useCurrent())) {
                    continue;
                }

                if ($field instanceof ComposedField) {
                    $decomposed = $field->decompose()[$this->getModelClass()] ?? [];

                    if (Arr::hasAny($definition, $decomposed)) {
                        $fields = array_merge($fields, $decomposed);
                        $names = array_merge($names, array_keys($decomposed));

                        continue;
                    }
                }

                $definition[$name] = $field->getOwner()->generateFieldValue($field);
            }
        }

        return $definition;
    }

    /**
     * Get a new factory instance for the given model name.
     *
     * @param  string $modelName
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
     * @param  string $modelName
     * @return mixed
     */
    public static function createForModel(string $modelName)
    {
        return $modelName::factory()->create();
    }

    /**
     * Make with a factory.
     *
     * @param  string $modelName
     * @return mixed
     */
    public static function makeForModel(string $modelName)
    {
        return $modelName::factory()->make();
    }

    /**
     * Proxy dynamic factory methods onto their proper methods.
     *
     * @param  string|mixed $method
     * @param  array|mixed  $parameters
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
            return $this->for($factory->state(($parameters[0] ?? [])), $relationship);
        } else if (Str::startsWith($method, 'has')) {
            return $this->has(
                $factory
                    ->count(is_numeric(($parameters[0] ?? null)) ? $parameters[0] : 1)
                    ->state((is_callable(($parameters[0] ?? null)) || is_array(($parameters[0] ?? null))) ? $parameters[0] : ($parameters[1] ?? [])),
                $relationship
            );
        }
    }
}
