<?php

namespace Stillat\Meerkat\Core\Contracts\Comments;

/**
 * Converts a comment prototype into a CommentContract instance
 *
 * The comment factory is responsible for constructing a valid instance
 * of Stillat\Meerkat\Core\Contracts\Comments\CommentContract. It is up
 * to the implementer of Meerkat Core to supply a factory; this allows
 * different implementations to use their own base classes or objects.
 *
 * @since 2.0.0
 */
interface CommentFactoryContract
{

    /**
     * Converts the comment prototype into an instance of CommentContract.
     *
     * @param  array $protoComment The comment prototype.
     * @return CommentContract
     */
    public function makeComment($protoComment);

}
