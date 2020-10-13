<?php

namespace Stillat\Meerkat\Core\Guard;

use Stillat\Meerkat\Core\Comments\CommentManager;
use Stillat\Meerkat\Core\Comments\VariableSuccessResult;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Data\RuntimeContext;
use Stillat\Meerkat\Core\Exceptions\DataQueryException;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Exceptions\InconsistentCompositionException;

/**
 * Class SpamCleaner
 *
 * Provides utilities to remove all comments currently marked as spam.
 *
 * @package Stillat\Meerkat\Core\Guard
 * @since 2.0.8
 */
class SpamCleaner
{

    /**
     * The DataQuery instance.
     *
     * @var DataQuery
     */
    protected $dataQuery = null;

    /**
     * The CommentManager instance.
     *
     * @var CommentManager
     */
    protected $manager = null;

    public function __construct(DataQuery $query, CommentManager $manager)
    {
        $this->dataQuery = $query;
        $this->manager = $manager;
        $this->dataQuery->withContext(new RuntimeContext())->filterBy('is:spam(true)');
    }

    /**
     * Removes all comments currently marked as spam.
     *
     * @throws FilterException
     * @throws DataQueryException
     * @throws InconsistentCompositionException
     * @return VariableSuccessResult
     */
    public function deleteAllSpam()
    {
        $spamComments = array_keys($this->manager->queryAll($this->dataQuery)->getData());
        return $this->manager->getStorageManager()->removeAll($spamComments);
    }

}
