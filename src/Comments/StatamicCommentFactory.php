<?php

namespace Stillat\Meerkat\Comments;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentFactoryContract;

class StatamicCommentFactory implements CommentFactoryContract
{

    /**
     * Converts the comment prototype into an instance of CommentContract.
     *
     * @param array $protoComment The comment prototype.
     * @return CommentContract
     */
    public function makeComment($protoComment)
    {
        // TODO: Implement makeComment() method.
    }

}
