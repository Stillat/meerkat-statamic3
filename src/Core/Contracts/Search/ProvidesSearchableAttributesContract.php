<?php

namespace Stillat\Meerkat\Core\Contracts\Search;

/**
 * Interface ProvidesSearchableAttributesContract
 *
 * Indicates that the object has searchable attributes.
 *
 * @since 2.0.0
 */
interface ProvidesSearchableAttributesContract
{
    /**
     * Gets the searchable attributes of an object instance.
     *
     * @return array
     */
    public function getSearchableAttributes();
}
