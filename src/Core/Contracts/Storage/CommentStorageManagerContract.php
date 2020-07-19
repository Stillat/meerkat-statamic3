<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

interface CommentStorageManagerContract
{

    public function getCommentsForThreadId($threadId);

    public function isChildOf($child, $testParent);
    public function isParentOf($parent, $testChild);

}