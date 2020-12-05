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

use Illuminate\Support\ServiceProvider;
use Laramore\Traits\Provider\MergesConfig;
use Laramore\Fields\BaseField;
use Laramore\Mixins\FactoryField;

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
            __DIR__.'/../../config/field/factories.php', 'field.factories',
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
     * Mixin factory methods.
     *
     * @return void
     */
    public function bootingCallback()
    {
        // Type::define('factory_name');
        // Type::define('factory_parameters', []);
        BaseField::mixin(new FactoryField());
    }
}
