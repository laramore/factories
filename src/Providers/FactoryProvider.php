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
use Laramore\Facades\{
	Validation, Type
};
use Laramore\Contracts\{
    Provider\LaramoreProvider, Manager\LaramoreManager
};
use Laramore\Traits\Provider\MergesConfig;
use Laramore\Fields\BaseField;
use Laramore\Eloquent\Meta;

use Faker\Generator as FakerGenerator;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use Laramore\Factories\Factory;

class FactoryProvider extends ServiceProvider implements LaramoreProvider
{
    use MergesConfig;

    /**
     * Factory manager.
     *
     * @var Factory
     */
    protected static $manager;

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

        $this->app->singleton(EloquentFactory::class, function () {
            return static::generateManager();
        });

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
     * Generate the corresponded manager.
     *
     * @return LaramoreManager
     */
    public static function generateManager(): LaramoreManager
    {
        if (\is_null(static::$manager)) {
            static::$manager = new Factory(app(FakerGenerator::class));

            foreach (static::getDefaults() as $path) {
                static::$manager->load($path);
            }
        }

        return static::$manager;
    }

    /**
     * During booting, add our custom methods.
     *
     * @return void
     */
    public function boot()
    {

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
            $name = $this->getType()->getFactoryName();

            if (\is_null($name)) {
                return null;
            }

            if ($name === 'enum') {
                return app(FakerGenerator::class)->randomElement($this->getValues());
            }

            return $this->transform(
                $this->cast(app(FakerGenerator::class)->format(Str::camel($name)))
            );
        });
    }
}
