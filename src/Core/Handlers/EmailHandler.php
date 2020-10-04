<?php

namespace Stillat\Meerkat\Core\Handlers;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Exceptions\NotImplementedException;

class EmailHandler extends  BaseHandler
{

    public function handle(CommentContract $comment)
    {
        throw new NotImplementedException();
    }

}