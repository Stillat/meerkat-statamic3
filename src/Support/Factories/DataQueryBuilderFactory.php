<?php

namespace Stillat\Meerkat\Support\Factories;

use Illuminate\Contracts\Container\BindingResolutionException;
use Stillat\Meerkat\Core\Contracts\Data\QueryFactoryContract;
use Stillat\Meerkat\Core\Data\DataQuery;

/**
 * Class DataQueryBuilderFactory
 *
 * Provides utilities for construction DataQuery instances, with dependencies, at runtime.
 *
 * @since 2.0.0
 */
class DataQueryBuilderFactory implements QueryFactoryContract
{
    /**
     * Request a new DataQuery instance, with dependencies.
     *
     * @return DataQuery
     *
     * @throws BindingResolutionException
     */
    public function getNew()
    {
        return app()->make(DataQuery::class);
    }
}
