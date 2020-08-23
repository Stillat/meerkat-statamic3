<?php

namespace Stillat\Meerkat\Core\Comments;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
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
     * The data attributes, if any.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Indicates whether or not the comment should be permanently removed or not.
     *
     * @var boolean
     */
    protected $doSoftDelete = false;

    /**
     * The comment instance, if available.
     *
     * @var null|CommentContract
     */
    public $comment = null;

    /**
     * Indicates if the removal will remove other comments.
     *
     * @var bool
     */
    public $willRemoveOthers = false;

    /**
     * A list of the effected child comments, if any.
     *
     * @var array
     */
    public $effectedComments = [];

    /**
     * Sets an internal flag indicating that the comment should be hidden,
     * but not completely removed from the underlying storage system.
     *
     * @return CommentRemovalEventArgs
     */
    public function keep()
    {
       $this->doSoftDelete = true;

       return $this;
    }

    /**
     * Sets an internal flag indicating that the comment should be completely removed.
     *
     * @return CommentRemovalEventArgs
     */
    public function deletePermanently()
    {
        $this->doSoftDelete = false;

        return $this;
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
