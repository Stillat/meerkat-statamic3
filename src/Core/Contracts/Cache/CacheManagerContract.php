<?php

namespace Stillat\Meerkat\Core\Contracts\Cache;

interface CacheManagerContract
{

    /**
     * Sets the cache manager's internal scope.
     *
     * Subsequent cache requests should utilize the provided scope.
     *
     * @param string $cacheScope The cache scope.
     * @return CacheManagerContract
     */
    public function setScope($cacheScope);

    /**
     * Tests if an item exists in the cache.
     *
     * @param string $key The cached item's identifier.
     * @return boolean
     */
    public function hasCacheItem($key);

    /**
     * Generates a cache key, with respect to the current scope.
     *
     * @param string $key The cached item's identifier.
     * @return string
     */
    public function getCacheKey($key);

    /**
     * Tests if an item exists in the cache and is not expired.
     *
     * @param string $key The cached item's identifier.
     * @param float $timeToLiveInSeconds The maximum age of the cached item, in seconds.
     * @return mixed
     */
    public function hasCacheItemWithTtl($key, $timeToLiveInSeconds);

    /**
     * Attempts to place an item in the cache.
     *
     * @param string $key The cached item's identifier.
     * @param mixed $value The value to cache.
     * @return mixed
     */
    public function put($key, $value);

    /**
     * Attempts to retrieve an item from the cache.
     *
     * @param string $key The cached item's identifier.
     * @return mixed
     */
    public function get($key);

}