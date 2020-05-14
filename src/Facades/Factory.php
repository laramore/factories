<?php
/**
 * Add a facade for the Validation.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

class Factory extends Facade
{
    /**
     * Give the name of the accessor.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return EloquentFactory::class;
    }
}
