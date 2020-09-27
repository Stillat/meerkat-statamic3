<?php

namespace Stillat\Meerkat\Providers;

use Stillat\Meerkat\Core\Contracts\Data\Export\CsvWriterContract;
use Stillat\Meerkat\Core\Contracts\Data\PaginatorContract;
use Stillat\Meerkat\Core\Contracts\Data\QueryFactoryContract;
use Stillat\Meerkat\Core\Data\DataQueryFactory;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Data\Export\CsvWriter;
use Stillat\Meerkat\Data\Paginator;
use Stillat\Meerkat\Support\Factories\DataQueryBuilderFactory;

/**
 * Class DataServiceProvider
 *
 * Manages the registration of Meerkat's data querying, filtering, and management services.
 *
 * @package Stillat\Meerkat\Providers
 * @since 2.0.0
 */
class DataServiceProvider extends AddonServiceProvider
{

    public function register()
    {

        $this->app->bind(CsvWriterContract::class, CsvWriter::class);

        $this->app->singleton(CommentFilterManager::class, function ($app) {
            $manager = new CommentFilterManager();
            $manager->registerDefaultFilters();

            return $manager;
        });
        $this->app->bind(PaginatorContract::class, Paginator::class);

        $this->app->singleton(QueryFactoryContract::class, function ($app) {
            return new DataQueryBuilderFactory();
        });

        DataQueryFactory::$queryBuilderFactory = app()->make(QueryFactoryContract::class);

        // Automatically include helpers, if available.
        $filtersPath = base_path('meerkat/filters.php');

        if (file_exists($filtersPath)) {
            include_once $filtersPath;
        }
    }

}
