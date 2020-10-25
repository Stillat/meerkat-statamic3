<?php

namespace Stillat\Meerkat\Core\Handlers;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class BaseHandler
 * @package Stillat\Meerkat\Core\Comments\Handlers
 * @since 2.0.0
 */
abstract class BaseHandler
{

    /**
     * Performs some action after a comment submission was received.
     *
     * @param CommentContract $comment The comment.
     */
    public abstract function handle(CommentContract $comment);

}
