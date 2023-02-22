<?php

namespace Stillat\Meerkat\Core\Contracts\Data;

use Stillat\Meerkat\Core\Data\DataQuery;

/**
 * Interface QueryFactoryContract
 *
 * Provides a consistent API for creating new DataQuery instances, with dependencies.
 *
 * @since 2.0.0
 */
interface QueryFactoryContract
{
    /**
     * Returns a new instance of DataQuery.
     *
     * @return DataQuery
     */
    public function getNew();
}
