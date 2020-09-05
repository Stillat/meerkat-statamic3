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

    public abstract function handle(CommentContract $comment);

}