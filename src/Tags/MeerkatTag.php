<?php

namespace Stillat\Meerkat\Tags;

use Carbon\Carbon;
use Statamic\Tags\Parameters;
use Statamic\Tags\Tags;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\IsFilters;
use Stillat\Meerkat\Core\Support\TypeConversions;
use Stillat\Meerkat\Tags\Responses\CollectionRenderer;

/**
 * Class MeerkatTag
 *
 * Provides a structured way to implement Statamic tags in separate classes and
 * then utilize them from the single tags namespace Statamic provides to addons.
 *
 * @package Stillat\Meerkat\Tags
 * @since 2.0.0
 */
abstract class MeerkatTag extends Tags
{

    /**
     * The CommentFilterManager implementation instance.
     *
     * @var CommentFilterManager
     */
    protected $filterManager = null;

    public function __construct(CommentFilterManager $manager)
    {
        $this->filterManager = $manager;
    }

    /**
     * Copies the parent's tag context to the new instance.
     *
     * @param Tags $tags The original tag context.
     */
    public function setFromContext(Tags $tags)
    {
        $this->content = $tags->content;
        $this->context = $tags->context;
        $this->params = $tags->params;
        $this->tag = $tags->tag;
        $this->method = $tags->method;
        $this->isPair = $tags->isPair;
        $this->parser = $tags->parser;
    }

    /**
     * Checks if a parameter exists in the parameter collection.
     *
     * @param string $key The parameter name.
     * @return bool
     */
    public function hasParameterValue($key)
    {

        if ($this->params instanceof Parameters) {
            return $this->params->has($key);
        }

        return array_key_exists($key, $this->params);
    }

    /**
     * Gets the parameters, as an array.
     *
     * @return array
     */
    public function getParameterArray()
    {
        if ($this->params instanceof Parameters) {
            return $this->params->toArray();
        }

        return $this->params;
    }

    /**
     * Renders the tag content.
     *
     * @return string
     */
    abstract public function render();

    /**
     * Parses the provided tag parameters and applies any filters to the current data query.
     *
     * @param DataQuery $dataQuery The data query instance.
     */
    protected function applyParamFiltersToQuery($dataQuery)
    {
        $paramFilters = $this->getFiltersFromParams();
        $filterString = $this->getParameterValue(CollectionRenderer::PARAM_FILTER, null);

        if ($filterString !== null && mb_strlen(trim($filterString)) > 0) {
            $parsedFilters = $this->filterManager->parseFilterString($filterString);

            if ($parsedFilters !== null && is_array($parsedFilters)) {
                $paramFilters = array_merge($paramFilters, $parsedFilters);
            }
        }

        $hasTrashedFilter = array_key_exists(IsFilters::FILTER_IS_DELETED, $paramFilters);

        if ($hasTrashedFilter === false) {
            $paramFilters[IsFilters::FILTER_IS_DELETED] = 'is:deleted(false)';
        }

        unset($filterString);

        $primaryFilter = array_shift($paramFilters);

        $dataQuery->filterBy($primaryFilter);

        if (count($paramFilters) > 0) {
            foreach ($paramFilters as $filter) {
                $dataQuery->thenFilterBy($filter);
            }
        }
    }

    /**
     * Parses the Antlers parameters and converts them to filter expressions.
     *
     * @return array
     */
    protected function getFiltersFromParams()
    {
        $filters = [];

        if (TypeConversions::getBooleanValue(
                $this->getParameterValue(CollectionRenderer::PARAM_WITH_TRASHED, false)
            ) === true) {
            // We are using the wild-card filter here to allow any value.
            // `true` will match only those comments that are deleted
            // `false` will match only those comments that are not deleted
            $filters['is:deleted'] = 'is:deleted(*)';
        }

        if (TypeConversions::getBooleanValue(
                $this->getParameterValue(CollectionRenderer::PARAM_INCLUDE_SPAM, false)
            ) === false) {
            $filters['is:spam'] = 'is:spam(false)';
        }

        if (TypeConversions::getBooleanValue(
                $this->getParameterValue(CollectionRenderer::PARAM_UNAPPROVED, false)
            ) === false) {
            $filters['is:published'] = 'is:published(true)';
        }

        $untilFilter = $this->getParameterValue(CollectionRenderer::PARAM_UNTIL, null);
        $sinceFilter = $this->getParameterValue(CollectionRenderer::PARAM_SINCE, null);

        if ($untilFilter !== null && $sinceFilter !== null) {
            $sinceDate = $this->getDateTimeTimestamp($sinceFilter);
            $untilDate = $this->getDateTimeTimestamp($untilFilter);

            $filters['is:between'] = 'is:between(' . $sinceDate . ',' . $untilDate . ')';
        } elseif ($untilFilter === null && $sinceFilter !== null) {
            $sinceDate = $this->getDateTimeTimestamp($sinceFilter);

            $filters['is:after'] = 'is:after(' . $sinceDate . ')';
        } elseif ($untilFilter !== null && $sinceFilter === null) {
            $untilDate = $this->getDateTimeTimestamp($untilFilter);

            $filters['is:before'] = 'is:before(' . $untilDate . ')';
        }

        return $filters;
    }

    /**
     * Attempts to retrieve the value of the named parameter.
     *
     * @param string $key The name of the parameter.
     * @param null|mixed $default The default value to return.
     * @return mixed|null
     */
    public function getParameterValue($key, $default = null)
    {
        if ($this->params instanceof Parameters) {
            return $this->params->get($key, $default);
        }

        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }

        return $default;
    }

    /**
     * Converts the value to a UNIX-like timestamp.
     *
     * @param string $value The input value.
     * @return int|string
     */
    protected function getDateTimeTimestamp($value)
    {
        $timestampValue = $value;

        if (is_string($value) && mb_strlen(trim($value)) == 10) {
            $timestampValue = $value;
        } else {
            $timestampValue = Carbon::parse($timestampValue)->timestamp;
        }

        return $timestampValue;
    }

    /**
     * Removes any Antlers specific filter restrictions.
     */
    protected function removeFilterRestrictions()
    {
        $this->filterManager->restrictFilter('thread:in', []);
    }

}
