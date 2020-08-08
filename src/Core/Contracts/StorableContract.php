<?php

namespace Stillat\Meerkat\Core\Contracts;

/**
 * Interface StorableContract
 *
 * Provides a consistent API for interacting with persistent objects.
 *
 * @package Stillat\Meerkat\Core\Contracts
 * @since 2.0.0
 */
interface StorableContract
{

    /**
     * Sets the storage path.
     *
     * @param string $path
     *
     * @return void
     */
    public function setPath($path);

    /**
     * Gets the storage path.
     *
     * @return string
     */
    public function getPath();

    /**
     * Attempts to remove the current object instance.
     *
     * @return boolean
     */
    public function delete();

}
