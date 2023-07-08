<?php

namespace Stillat\Meerkat\Core\Data\Filters;

use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\IsFilters;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\Like;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\NotLike;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\Search;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\ThreadIn;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\UserFromAuth;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\UserIn;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\Where;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\WhereIn;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\WhereNotIn;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Parsing\ExpressionParser;
use Stillat\Meerkat\Core\Support\Str;

/**
 * Class CommentFilterManager
 *
 * Manages comment filters and their runtime contexts.
 *
 * @since 1.5.85
 */
class CommentFilterManager
{
    /**
     * The current parameter mapping.
     *
     * @var array
     */
    protected $paramMapping = [];

    /***
     * A mapping of a filters required parameters.
     *
     * @var array
     */
    protected $filterRequiredParamMapping = [];

    /**
     * A collection of registered filters.
     *
     * @var CommentFilter[]
     */
    private $filters = [];

    /**
     * A mapping of group names to many filters.
     *
     * @var array
     */
    private $groups = [];

    /**
     * The current identity.
     *
     * @var AuthorContract|null
     */
    private $user = null;

    /**
     * A collection of registered resolvable variables.
     *
     * @var array
     */
    private $resolvableItems = [];

    /**
     * A cache of already resolved items.
     *
     * @var array
     */
    private $resolvedCache = [];

    /**
     * Registers a filter group.
     *
     * @param  string  $groupName The group name.
     * @param  string  $filters The filters to use when this group is referenced.
     */
    public function filterGroup($groupName, $filters)
    {
        $this->groups['@'.$groupName] = $filters;
    }

    /**
     * Returns all registered filter groups.
     *
     * @return array
     *
     * @since 2.1.21
     */
    public function getFilterGroups()
    {
        return $this->groups;
    }

    /**
     * Registers the default Meerkat filters.
     */
    public function registerDefaultFilters()
    {
        (new Where())->register($this);
        (new WhereIn())->register($this);
        (new WhereNotIn())->register($this);
        (new Like())->register($this);
        (new NotLike())->register($this);
        (new UserFromAuth())->register($this);
        (new UserIn())->register($this);
        (new ThreadIn())->register($this);
        (new IsFilters())->register($this);
        (new Search())->register($this);
    }

    /**
     * Checks if the provided filter is a group and returns the group, else the filter.
     *
     * @param  string  $filter The filter to check.
     * @return string
     */
    public function getFilterMap($filter)
    {
        if (Str::startsWith($filter, '@')) {
            if (array_key_exists($filter, $this->groups)) {
                return $this->groups[$filter];
            }
        }

        return $filter;
    }

    /**
     * Registers a new thread filter.
     *
     * @param  string  $filterName The name of the filter.
     * @param  callable  $callback The filter callback.
     * @param  string  $params Optional parameter mappings.
     */
    public function filter($filterName, $callback, $params = '')
    {
        $filterObject = new CommentFilter();
        $filterObject->setCallback($callback);

        $this->filters[$filterName] = $filterObject;
        $this->filterRequiredParamMapping[$filterName] = $params;
    }

    /**
     * Registers a new thread filter.
     *
     * @param  string  $filterName The name of the filter.
     * @param  callable  $callback The filter callback.
     * @param  string  $params Optional parameter mappings.
     * @param  array  $supportedTags The filter's supported tags.
     */
    public function filterWithTagContext($filterName, $callback, $params = '', $supportedTags = [])
    {
        $filterObject = new CommentFilter();
        $filterObject->setCallback($callback);
        $filterObject->setSupportedTags($supportedTags);

        $this->filters[$filterName] = $filterObject;
        $this->filterRequiredParamMapping[$filterName] = $params;
    }

    /**
     * Lets Meerkat know how to resolve a variable.
     *
     * @param  string  $variableName The resolvable name.
     * @param  callable  $callback The function to execute when this value is requested.
     */
    public function resolve($variableName, $callback)
    {
        $filterVariable = new FilterVariable();
        $filterVariable->setCallback($callback);

        $this->resolvableItems['$'.$variableName] = $filterVariable;
    }

    /**
     * Restricts a filter to specific tag contexts.
     *
     * @param  string  $filterName The filter name.
     * @param  array  $tagContexts The filter tag contexts.
     */
    public function restrictFilter($filterName, $tagContexts)
    {
        $filterName = $this->getFilterName($filterName);

        if ($filterName !== null) {
            if (array_key_exists($filterName, $this->filters)) {
                $this->filters[$filterName]->setSupportedTags($tagContexts);
            }
        }
    }

    /**
     * Parses the filter name from the input string.
     *
     * @param  string  $filterName The filter name input.
     * @return string|null
     */
    protected function getFilterName($filterName)
    {
        if (Str::contains($filterName, '(')) {
            $filterParts = explode('(', $filterName);

            if (count($filterParts) == 2) {
                $filterName = trim($filterParts[0]);
            } else {
                return null;
            }
        }

        return $filterName;
    }

    /**
     * Removes tag context restrictions from the provided filter name.
     *
     * @param  string  $filterName The filter name.
     */
    public function removeRestrictions($filterName)
    {
        $filterName = $this->getFilterName($filterName);

        if ($filterName !== null) {
            if (array_key_exists($filterName, $this->filters)) {
                $this->filters[$filterName]->setSupportedTags([]);
            }
        }
    }

    /**
     * Runs the requested filter against the comments within context.
     *
     * @param  array  $queryFilter The name of the filter.
     * @param  array  $comments The comments to filter.
     * @param  null  $context The parser context.
     * @param  string  $tagContext The tag context.
     * @return mixed|null
     *
     * @throws FilterException
     */
    public function runFilter($queryFilter, $comments, $context = null, $tagContext = '')
    {
        $filterName = $queryFilter[ExpressionParser::KEY_NAME];
        $runtimeParameters = $queryFilter[ExpressionParser::KEY_INPUT];

        $parameters = [];

        // Iterate all runtime parameters and convert any resolvable values to their actual values.
        foreach ($runtimeParameters as $param) {
            $paramValueName = $param[ExpressionParser::KEY_VALUE];

            if (Str::startsWith($paramValueName, '$')) {
                if (array_key_exists($paramValueName, $this->resolvableItems)) {
                    if (array_key_exists($paramValueName, $this->resolvedCache) === false) {
                        /** @var FilterVariable $resolver */
                        $resolver = $this->resolvableItems[$paramValueName];

                        $resolver->setContext($context);
                        $resolver->setParameters([]);
                        $resolver->setUser($this->getUser());

                        $this->resolvedCache[$paramValueName] = $resolver->getValue();
                    }

                    $resolvedValue = $this->resolvedCache[$paramValueName];
                    $currentType = $param[ExpressionParser::KEY_TYPE];

                    $parameters[] = [
                        ExpressionParser::KEY_VALUE => $resolvedValue,
                        ExpressionParser::KEY_TYPE => $currentType,
                    ];
                } else {
                    throw new FilterException('Could not find resolvable item: '.$paramValueName);
                }
            } else {
                $parameters[] = $param;
            }
        }

        if ($this->hasFilter($filterName)) {
            $requiredParameters = [];

            if (array_key_exists($filterName, $this->filterRequiredParamMapping)) {
                $requiredParameters = explode(ExpressionParser::TOKEN_INPUT_DELIMITER, $this->filterRequiredParamMapping[$filterName]);
            }

            $parameters = ExpressionParser::mapParameters($requiredParameters, $parameters);

            $filter = $this->filters[$filterName];
            $filter->setContext($context);
            $filter->setName($filterName);
            $filter->setParameters($parameters);
            $filter->setUser($this->getUser());

            $filterTags = $filter->getSupportedTags();

            if (count($filterTags) > 0) {
                if (in_array($tagContext, $filterTags) == false) {
                    if (is_array($queryFilter)) {
                        if (array_key_exists('name', $queryFilter)) {
                            $queryFilter = $queryFilter['name'];
                        } else {
                            $queryFilter = '[unknown]';
                        }
                    }
                    throw new FilterException($queryFilter.' is not supported by '.$tagContext);
                }
            }

            return $filter->runFilter($comments);
        }

        throw new FilterException($queryFilter.' Meerkat filter not found.');
    }

    /**
     * Gets the currently used user identity, if any.
     *
     * @return mixed|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * The current user identity.
     *
     * @param  mixed|null  $identity
     */
    public function setUser(AuthorContract $identity)
    {
        $this->user = $identity;
    }

    /**
     * Checks if a filter exists.
     *
     * @param  string  $filterName The filter name.
     * @return bool
     */
    public function hasFilter($filterName)
    {
        return array_key_exists($filterName, $this->filters);
    }
}
