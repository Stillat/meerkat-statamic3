<?php

namespace Stillat\Meerkat\Providers;

use Stillat\Meerkat\Core\Contracts\Data\PaginatorContract;
use Stillat\Meerkat\Data\Paginator;

class DataServiceProvider extends AddonServiceProvider
{

    public function register()
    {
        $this->app->bind(PaginatorContract::class, Paginator::class);
    }

}
