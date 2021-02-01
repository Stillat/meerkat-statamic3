<?php

namespace Stillat\Meerkat\Contracts\Configuration;

/**
 * Interface SupplementalStorageManagerContract
 *
 * Represents a storage driver for manipulating system-wide Meerkat configuration items.
 *
 * @package Stillat\Meerkat\Contracts\Configuration
 * @since 2.3.0
 */
interface SupplementalStorageManagerContract
{

    /**
     * Attempts to save the provided configuration.
     *
     * @param array $configSettings The configuration.
     * @return bool
     */
    public function saveConfiguration($configSettings);

    /**
     * Retrieves all requested namespaces that can be managed.
     *
     * @param array $namespaces The configuration namespaces to retrieve.
     * @param array $managedNamespaces A list of all managed namespaces.
     * @return array
     */
    public function getAllSupplementalConfiguration($namespaces, $managedNamespaces);

    /**
     * Retrieves a hash value, representing the persisted configuration state.
     *
     * @param array $namespaces The configuration namespaces to analyze.
     * @param array $managedNamespaces A list of all managed namespaces.
     * @return string
     */
    public function getUpdateHash($namespaces, $managedNamespaces);

}
