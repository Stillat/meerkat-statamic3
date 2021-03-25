<?php

namespace Stillat\Meerkat\Tags;

use Carbon\Carbon;
use Statamic\Tags\Parameters;
use Statamic\Tags\Tags;
use Stillat\Meerkat\Core\Data\DataQuery;
use Stillat\Meerkat\Core\Data\Filters\CommentFilterManager;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\IsFilters;
use Stillat\Meerkat\Core\Exceptions\FilterParserException;
use Stillat\Meerkat\Core\Parsing\ExpressionParser;
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

    protected $expressionParser = null;

    public function __construct(CommentFilterManager $manager, ExpressionParser $expressionParser)
    {
        $this->filterManager = $manager;
        $this->expressionParser = $expressionParser;
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
     * @throws FilterParserException
     */
    protected function applyParamFiltersToQuery($dataQuery)
    {
        $this->expressionParser->setFilterGroups($this->filterManager->getFilterGroups());

        $paramFilters = $this->getFiltersFromParams();

        $filterString = $this->getParameterValue(CollectionRenderer::PARAM_FILTER, null);

        if ($filterString !== null && mb_strlen(trim($filterString)) > 0) {
            $filters = $this->expressionParser->parse($filterString);

            if ($filters !== null && is_array($filters)) {
                $paramFilters = array_merge($paramFilters, $filters);
            }
        }

        if (count($paramFilters) === 0 && $filterString === null) {
            $trashedFilter = $this->expressionParser->parse(ExpressionParser::build(IsFilters::FILTER_IS_DELETED, ['false']));

            if ($trashedFilter !== null && is_array($trashedFilter) && count($trashedFilter) === 1) {
                $paramFilters[] = array_pop($trashedFilter);
            }

            $publishedFilter = $this->expressionParser->parse(ExpressionParser::build(IsFilters::FILTER_IS_PUBLISHED, ['true']));

            if ($publishedFilter !== null && is_array($publishedFilter) && count($publishedFilter) === 1) {
                $paramFilters[] = array_pop($publishedFilter);
            }

            $notSpamFilter = $this->expressionParser->parse(ExpressionParser::build(IsFilters::FILTER_IS_SPAM, ['false']));

            if ($notSpamFilter !== null && is_array($notSpamFilter) && count($notSpamFilter) === 1) {
                $paramFilters[] = array_pop($notSpamFilter);
            }
        }

        $hasTrashedFilter = ExpressionParser::hasFilter(IsFilters::FILTER_IS_DELETED, $paramFilters);

        if ($hasTrashedFilter === false) {
            $trashedFilter = $this->expressionParser->parse(ExpressionParser::build(IsFilters::FILTER_IS_DELETED, ['false']));

            if ($trashedFilter !== null && is_array($trashedFilter) && count($trashedFilter) === 1) {
                $paramFilters[] = array_pop($trashedFilter);
            }
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
     * @throws FilterParserException
     */
    protected function getFiltersFromParams()
    {
        $filters = [];

        if ($this->hasParameterValue(CollectionRenderer::PARAM_WITH_TRASHED)) {
            if (TypeConversions::getBooleanValue(
                    $this->getParameterValue(CollectionRenderer::PARAM_WITH_TRASHED, false)
                ) === true) {
                // We are using the wild-card filter here to allow any value.
                // `true` will match only those comments that are deleted
                // `false` will match only those comments that are not deleted
                $filters[] = ExpressionParser::build(IsFilters::FILTER_IS_DELETED, ['*']);
            }
        }

        if ($this->hasParameterValue(CollectionRenderer::PARAM_INCLUDE_SPAM)) {
            if (TypeConversions::getBooleanValue(
                    $this->getParameterValue(CollectionRenderer::PARAM_INCLUDE_SPAM, false)
                ) === false) {
                $filters[] = ExpressionParser::build(IsFilters::FILTER_IS_SPAM, ['false']);
            } else {
                $filters[] = ExpressionParser::build(IsFilters::FILTER_IS_SPAM, ['*']);
            }
        }

        if ($this->hasParameterValue(CollectionRenderer::PARAM_UNAPPROVED)) {
            if (TypeConversions::getBooleanValue(
                    $this->getParameterValue(CollectionRenderer::PARAM_UNAPPROVED, false)
                ) === true) {
                $filters[] = ExpressionParser::build(IsFilters::FILTER_IS_PUBLISHED, ['*']);
            }
        }

        $untilFilter = $this->getParameterValue(CollectionRenderer::PARAM_UNTIL, null);
        $sinceFilter = $this->getParameterValue(CollectionRenderer::PARAM_SINCE, null);

        if ($untilFilter !== null && $sinceFilter !== null) {
            $sinceDate = $this->getDateTimeTimestamp($sinceFilter);
            $untilDate = $this->getDateTimeTimestamp($untilFilter);

            $filters[] = ExpressionParser::build(IsFilters::FILTER_IS_BETWEEN, [$sinceDate, $untilDate]);
        } elseif ($untilFilter === null && $sinceFilter !== null) {
            $sinceDate = $this->getDateTimeTimestamp($sinceFilter);

            $filters[] = ExpressionParser::build(IsFilters::FILTER_IS_AFTER, [$sinceDate]);
        } elseif ($untilFilter !== null && $sinceFilter === null) {
            $untilDate = $this->getDateTimeTimestamp($untilFilter);

            $filters[] = ExpressionParser::build(IsFilters::FILTER_IS_BEFORE, [$untilDate]);
        }

        // Convert our temporary filters into a filter expression string.
        $filterString = implode(ExpressionParser::TOKEN_FILTER_DELIMITER, $filters);

        return $this->expressionParser->parse($filterString);
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
