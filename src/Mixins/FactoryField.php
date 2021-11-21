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

use Faker\Generator as Faker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laramore\Contracts\Field\ManyRelationField;
use Laramore\Factories\Factory;
use Laramore\Fields\Constraint\BaseIndexableConstraint;

class FactoryField
{
    /**
     * Return factory field config.
     *
     * @return mixed
     */
    public function getFactory()
    {
        return function () {
            $faker = app(Faker::class);

            /** @var \Laramore\Fields\BaseField $this */
            if ($this->getConstraintHandler()->count(BaseIndexableConstraint::UNIQUE) > 0 ||
                $this->getConstraintHandler()->count(BaseIndexableConstraint::INDEX) > 0 ||
                $this->getConstraintHandler()->count(BaseIndexableConstraint::PRIMARY) > 0 ||
                $this->getConstraintHandler()->count(BaseIndexableConstraint::MORPH_INDEX) > 0) {
                $faker = $faker->unique();
            }

            return $faker;
        };
    }

    /**
     * Return factory field config.
     *
     * @return mixed
     */
    public function getFactoryConfig()
    {
        return function (string $path=null, $default=null) {
            if (\is_null($path)) {
                return $this->config['factories'];
            }

            return Arr::get($this->config['factories'], $path, $default);
        };
    }

    /**
     * Return factory field formater.
     *
     * @return string|null
     */
    public function getFactoryFormater()
    {
        return function () {
            /** @var \Laramore\Fields\BaseField $this */
            $formater = $this->getFactoryConfig('formater');

            return \is_null($formater) ? $formater : Str::camel($formater);
        };
    }

    /**
     * Return factory formater parameters.
     *
     * @return array
     */
    public function getFactoryParameters()
    {
        return function () {
            /** @var \Laramore\Fields\BaseField $this */
            $name = $this->getFactoryFormater();
            $parameters = $this->getFactoryConfig('parameters', []);

            if ($name === 'randomElement' && \method_exists($this, 'getValues')) {
                return [$this->getValues()];
            }

            if ($name === 'randomFloat' && $this->hasProperty('totalDigits') && $this->hasProperty('decimalDigits')) {
                if (\count($parameters) === 0) {
                    $parameters[] = $this->totalDigits;
                }
            }

            return $parameters;
        };
    }

    /**
     * Generate a value with factory field.
     *
     * @return mixed
     */
    public function generate()
    {
        return function () {
            /** @var \Laramore\Fields\BaseField $this */
            $name = $this->getFactoryFormater();

            if (\is_null($name)) {
                return $this->getDefault();
            }

            if ($name === 'password') {
                return $this->getFactoryConfig('password');
            }

            if ($name === 'wordsObject') {
                $keys = $this->getFactory()->format('words');
                $values = $this->getFactory()->format('words');

                return array_combine($keys, $values);
            }

            $parameters = $this->getFactoryParameters();

            if ($name === 'relation') {
                $factory = Factory::factoryForModel($this->getTargetModel());

                foreach ($parameters as $method => $args) {
                    $args = \is_array($args) ? $args : [$args];

                    $factory = \call_user_func([$factory, $method], ...$args);
                }

                return $factory;
            }

            if ($name === 'randomRelation') {
                $builder = $this->getTargetModel()::query()->inRandomOrder();

                foreach ($parameters as $method => $args) {
                    $args = \is_array($args) ? $args : [$args];

                    $builder = \call_user_func([$builder, $method], ...$args);
                }

                return $this instanceof ManyRelationField
                    ? $builder->get()
                    : $builder->first();
            }

            if ($name === 'randomFloat') {
                return $this->getFactory()->format('randomNumber', $parameters) / pow(10, $this->decimalDigits);
            }

            return $this->getFactory()->format(
                $name, $parameters
            );
        };
    }
}
