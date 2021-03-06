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


class FactoryField
{
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
                $maxDigits = ($this->totalDigits - $this->decimalDigits);
                $max = (pow(10, ($maxDigits + 1)) - 1);

                if (\count($parameters) === 0) {
                    $parameters[] = (- $max);
                }

                if (\count($parameters) === 1) {
                    $parameters[] = $max;
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

            return app(Faker::class)->format(
                $name, $parameters
            );
        };
    }
}
