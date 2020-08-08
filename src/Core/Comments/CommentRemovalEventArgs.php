<?php

namespace Stillat\Meerkat\Core\Comments;

use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\DataObject;

/**
 * Class CommentRemovalEventArgs
 *
 * Represents a Comment during a thread mutation request
 *
 * @package Stillat\Meerkat\Core\Threads
 * @since 2.0.0
 */
class CommentRemovalEventArgs implements DataObjectContract
{
    use DataObject;

    /**
     * Indicates whether or not the comment should be permanently removed or not.
     *
     * @var boolean
     */
    protected $doSoftDelete = false;

    /**
     * Sets an internal flag indicating that the comment should be hidden,
     * but not completely removed from the underlying storage system.
     *
     * @return void
     */
    public function keep()
    {
        $this->doSoftDelete = true;
    }

    /**
     * Sets an internal flag indicating that the comment should be completely removed.
     *
     * @return void
     */
    public function deletePermanently()
    {
        $this->doSoftDelete = false;
    }

    /**
     * Returns a value indicating if the comment should be permanently removed or not.
     *
     * @return boolean
     */
    public function shouldKeep()
    {
        return $this->doSoftDelete;
    }

}
