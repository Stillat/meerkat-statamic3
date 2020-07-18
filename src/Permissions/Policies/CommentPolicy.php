<?php

namespace Stillat\Meerkat\Permissions\Policies;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

class CommentPolicy
{

    public function update($user, CommentContract $comment)
    {
        dd(' oh hai ', $user, $comment);
    }

}