<?php

namespace Stillat\Meerkat\Core\Exceptions;

/**
 * Class CommentNotFoundException
 *
 * Thrown when a comment could not be found.
 *
 * @since 2.0.0
 */
class CommentNotFoundException extends MeerkatCoreException
{
    /**
     * The identifier of the comment that could not be found.
     *
     * @var null|string
     */
    public $commentId = null;
}
