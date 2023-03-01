<?php

namespace Stillat\Meerkat\Core\Contracts;

/**
 * Interface UniqueIdentifierGeneratorContract
 *
 * Provides a standard API for generating unique identifiers.
 *
 * @since 2.0.0
 */
interface UniqueIdentifierGeneratorContract
{
    /**
     * Requests a new unique identifier.
     *
     * @return string|int
     */
    public function newId();
}
