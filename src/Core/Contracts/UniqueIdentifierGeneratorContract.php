<?php

namespace Stillat\Meerkat\Core\Contracts;

/**
 * Interface UniqueIdentifierGeneratorContract
 *
 * Provides a standard API for generating unique identifiers.
 *
 * @package Stillat\Meerkat\Core\Contracts
 * @since 2.0.0
 */
interface UniqueIdentifierGeneratorContract
{

    /**
     * Requests a new unique identifier.
     *
     * @return string|integer
     */
    public function newId();

}
