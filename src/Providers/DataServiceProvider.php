<?php

namespace Stillat\Meerkat\Providers;

use Stillat\Meerkat\Core\Contracts\Data\PaginatorContract;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Data\Paginator;

class DataServiceProvider extends AddonServiceProvider
{

    public function register()
    {
        $this->app->singleton(CommentFilterManager::class, function ($app) {
            return $app->make(CommentFilterManager::class);
        });
        $this->app->bind(PaginatorContract::class, Paginator::class);
    }

}
