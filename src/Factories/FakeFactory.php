<?php
/**
 * Factory fake generator.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2020
 * @license MIT
 */

namespace Laramore\Factories;

class FakeFactory extends Factory
{
    /**
     * Define model name for this fake factory.
     *
     * @param string $modelName
     */
    public function __construct(string $modelName)
    {
        $this->model = $modelName;

        parent::__construct();
    }
}
