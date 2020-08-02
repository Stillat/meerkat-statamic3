<?php

namespace Stillat\Meerkat\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;

class Comments extends Facade
{

    protected static function getFacadeAccessor()
    {
        return CommentFilterManager::class;
    }

}
