<?php

namespace Stillat\Meerkat\Core\Threads;

use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\DataObject;

/**
 * Class ThreadRemovalEventArgs
 *
 * Represents a Thread during a thread mutation request
 *
 * @package Stillat\Meerkat\Core\Threads
 * @since 2.0.0
 */
class ThreadRemovalEventArgs implements DataObjectContract
{
    use DataObject;

    /**
     * The data attributes, if any.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Indicates whether or not the thread should be permanently removed or not.
     *
     * @var boolean
     */
    protected $doSoftDelete = false;

    /**
     * The thread's string identifier.
     *
     * @var null|string
     */
    public $threadId = null;

    /**
     * Sets an internal flag indicating that the thread should be hidden,
     * but not completely removed from the underlying storage system.
     *
     * @return ThreadRemovalEventArgs
     */
    public function keep()
    {
        $this->doSoftDelete = true;

        return $this;
    }

    /**
     * Sets an internal flag indicating that the thread should be completely removed.
     *
     * @return ThreadRemovalEventArgs
     */
    public function deletePermanently()
    {
        $this->doSoftDelete = false;

        return $this;
    }

    /**
     * Returns a value indicating if the thread should be permanently removed or not.
     *
     * @return boolean
     */
    public function shouldKeep()
    {
        return $this->doSoftDelete;
    }

}
