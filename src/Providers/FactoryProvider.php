<?php
/**
 * Prepare the package.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Providers;

use Illuminate\Support\{
    ServiceProvider, Str
};
use Laramore\Facades\Type;
use Laramore\Traits\Provider\MergesConfig;
use Laramore\Fields\BaseField;

use Faker\Generator as FakerGenerator;
use Laramore\Contracts\Field\ManyRelationField;
use Laramore\Factories\Factory;

class FactoryProvider extends ServiceProvider
{
    use MergesConfig;

    /**
     * Before booting, create our definition for migrations.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/type/configurations.php', 'type.configurations',
        );

        $this->mergeConfigFrom(
            __DIR__.'/../../config/field/proxy.php', 'field.proxy',
        );

        $this->mergeConfigFrom(
            __DIR__.'/../../config/proxy.php', 'proxy',
        );

        $this->app->booting([$this, 'bootingCallback']);
    }

    /**
     * Return the default values for the manager of this provider.
     *
     * @return array
     */
    public static function getDefaults(): array
    {
        return [app()->databasePath('factories')];
    }


    /**
     * Before booting, add a new validation definition and fix increment default value.
     * If the manager is locked during booting we need to reset it.
     *
     * @return void
     */
    public function bootingCallback()
    {
        Type::define('factory_name');
        Type::define('factory_parameters', []);

        $this->setMacros();
    }

    /**
     * Add all required macros for validations.
     *
     * @return void
     */
    protected function setMacros()
    {
        BaseField::macro('generate', function () {
            /** @var \Laramore\Fields\BaseField $this */
            $name = $this->getType()->getFactoryName();
            $parameters = $this->getType()->getFactoryParameters();
            $faker = app(FakerGenerator::class);

            if (\is_null($name)) {
                return $this->getDefault();
            }

            if ($name === 'enum') {
                return app(FakerGenerator::class)->randomElement($this->getValues());
            }

            if ($name === 'relation' || $name === 'reversed_relation') {
                if ($this instanceof ManyRelationField) {
                    return Factory::factoryForModel($this->getTargetModel())->count(
                        $faker->numberBetween(0, 5)
                    );
                }

                return Factory::factoryForModel($this->getTargetModel());
            }

            if ($name === 'morph_relation' || $name === 'reversed_morph_relation') {
                if ($this instanceof ManyRelationField) {
                    return Factory::factoryForModel($this->getTargetModel())->count(
                        $faker->numberBetween(0, 5)
                    );
                }

                return Factory::factoryForModel(app(FakerGenerator::class)->randomElement($this->getTargetModels()));
            }

            if ($name === 'randomFloat') {
                $maxDigits = $this->totalDigits - $this->decimalDigits;
                $max = pow(10, $maxDigits + 1) - 1;

                if (\count($parameters) === 0) {
                    $parameters[] = - $max;
                }

                if (\count($parameters) === 1) {
                    $parameters[] = $max;
                }
            }

            return $this->cast($faker->format(
                Str::camel($name), $parameters
            ));
        });
    }
}
