<?php

namespace Stillat\Meerkat\Core\Contracts\Data;

/**
 * Interface DataSetContract
 *
 * Represents a basic Core data set.
 *
 * @package Stillat\Meerkat\Core\Contracts\Data
 * @since 2.0.0
 */
interface DataSetContract
{

    /**
     * Returns the data set and any additional meta data properties.
     *
     * @return array
     */
    public function toArray();

}
