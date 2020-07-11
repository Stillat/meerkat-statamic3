<?php

namespace Stillat\Meerkat\Core\Contracts\Storage;

interface CommentStorageManagerContract
{

    public function isChildOf($child, $testParent);
    public function isParentOf($parent, $testChild);

}