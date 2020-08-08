<?php

namespace Stillat\Meerkat\Core\Data\Concerns;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Trait FlattensGroupedDataSets
 *
 * Provides utilities to flatten and iterate grouped datasets.
 *
 * @package Stillat\Meerkat\Core\Data\Concerns
 * @since 2.0.0
 */
trait IteratesDataSets
{

    /**
     * A cached copy of the flattened data set.
     *
     * @var null|CommentContract[]
     */
    protected $flattenedData = null;

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        $this->flattenDataset();

        return current($this->flattenedData);
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->flattenDataset();

        return next($this->flattenedData);
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        $this->flattenDataset();

        return isset($this->flattenedData[$this->key()]);
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return string|float|int|bool|null scalar on success, or null on failure.
     */
    public function key()
    {
        $this->flattenDataset();

        return key($this->flattenedData);
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->flattenDataset();

        reset($this->flattenedData);
    }

}
