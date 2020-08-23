<?php

namespace Stillat\Meerkat\Core\Comments;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\DataObject;

class CommentRestoringEventArgs implements  DataObjectContract
{
    use DataObject;

    /**
     * The data attributes, if any.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Indicates if the restoration should continue.
     *
     * @var bool
     */
    protected $shouldRestore = true;

    /**
     * The comment's identifier, if available.
     *
     * @var null|string
     */
    public $commentId = null;

    /**
     * The comment instance, if available.
     *
     * @var null|CommentContract
     */
    public $comment = null;

    /**
     * Requests that the restoration should continue.
     *
     * @return CommentRestoringEventArgs
     */
    public function allowRestore()
    {
        $this->shouldRestore = true;

        return $this;
    }

    /**
     * Requests that the restoration should not continue.
     *
     * @return CommentRestoringEventArgs
     */
    public function denyRestore()
    {
        $this->shouldRestore = false;

        return $this;
    }

    /**
     * Returns a value indicating if the restoration should continue.
     *
     * @return bool
     */
    public function shouldRestore()
    {
        return $this->shouldRestore;
    }

}