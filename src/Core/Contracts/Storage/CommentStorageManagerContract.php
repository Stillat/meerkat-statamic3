<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

interface CommentStorageManagerContract
{

    public function getCommentsForThreadId($threadId);

    public function save(CommentContract $comment);

    public function update(CommentContract $comment);

    public function isChildOf($child, $testParent);
    public function isParentOf($parent, $testChild);

}