<?php

namespace Stillat\Meerkat\Core\Data;

use Stillat\Meerkat\Core\Contracts\Data\QueryFactoryContract;

/**
 * Class DataQueryFactory
 *
 * Provides utilities for generating new DataQuery instances.
 *
 * @since 2.0.0
 */
class DataQueryFactory
{
    /**
     * The QueryFactoryContract implementation instance.
     *
     * @var null|QueryFactoryContract
     */
    public static $queryBuilderFactory = null;

    /**
     * Requests a new DataQuery instance.
     *
     * @return DataQuery|null
     */
    public static function newQuery()
    {
        if (DataQueryFactory::$queryBuilderFactory === null) {
            return null;
        }

        return DataQueryFactory::$queryBuilderFactory->getNew();
    }
}
