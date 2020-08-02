<?php

namespace Stillat\Meerkat\Core\Data\Filters;

use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\IsFilters;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\ThreadIn;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\UserFromAuth;
use Stillat\Meerkat\Core\Data\Filters\DefaultFilters\UserIn;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Support\Str;

class CommentFilterManager
{


    /**
     * The current parameter mapping.
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
     * @var array
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
     * @param string $groupName The group name.
     * @param string $filters The filters to use when this group is referenced.
     */
    public function filterGroup($groupName, $filters)
    {
        $this->groups['@' . $groupName] = $filters;
    }

    /**
     * Registers the default Meerkat filters.
     */
    public function registerDefaultFilters()
    {
        (new UserFromAuth())->register($this);
        (new UserIn())->register($this);
        (new ThreadIn())->register($this);
        (new IsFilters())->register($this);
    }

    /**
     * Checks if the provided filter is a group and returns the group, else the filter.
     *
     * @param string $filter The filter to check.
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
     * @param string $filterName The name of the filter.
     * @param callable $callback The filter callback.
     * @param string $params Optional parameter mappings.
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
     * @param string $filterName The name of the filter.
     * @param callable $callback The filter callback.
     * @param string $params Optional parameter mappings.
     * @param array $supportedTags The filter's supported tags.
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
     * @param string $variableName The resolvable name.
     * @param callable $callback The function to execute when this value is requested.
     */
    public function resolve($variableName, $callback)
    {
        $filterVariable = new FilterVariable();
        $filterVariable->setCallback($callback);

        $this->resolvableItems['$' . $variableName] = $filterVariable;
    }

    /**
     * Runs the requested filter against the comments within context.
     *
     * @param string $filterName The name of the filter.
     * @param array $comments The comments to filter.
     * @param array $parameters The run-time parameters.
     * @param null $context The parser context.
     * @param string $tagContext The tag context.
     * @return mixed|null
     * @throws FilterException
     */
    public function runFilter($filterName, $comments, $parameters, $context = null, $tagContext = '')
    {
        $originalName = $filterName;
        if (Str::contains($filterName, '(')) {
            $filterParts = explode('(', $filterName);

            if (count($filterParts) == 2) {
                $filterName = trim($filterParts[0]);
                $filterParamMapping = trim($filterParts[1]);

                if (Str::endsWith($filterParamMapping, ')') === false) {
                    throw new FilterException('Unmatched "(" in: ' . $originalName);
                }

                $filterParamMapping = mb_substr($filterParamMapping, 0, -1);

                if ($this->hasFilter($filterName)) {
                    // Remap the parameters.
                    if (array_key_exists($filterParamMapping, $parameters)) {
                        $parameters[$this->filterRequiredParamMapping[$filterName]] = $parameters[$filterParamMapping];
                    } else {
                        if (Str::startsWith($filterParamMapping, '$')) {
                            if (array_key_exists($filterParamMapping, $this->resolvableItems)) {
                                if (array_key_exists($filterParamMapping, $this->resolvedCache) == false) {
                                    /** @var FilterVariable $resolver */
                                    $resolver = $this->resolvableItems[$filterParamMapping];
                                    $resolver->setContext($context);
                                    $resolver->setParameters($parameters);
                                    $resolver->setUser($this->getUser());

                                    $this->resolvedCache[$filterParamMapping] = $resolver->getValue();
                                }

                                $parameters[$filterParamMapping] = $this->resolvedCache[$filterParamMapping];
                            } else {
                                throw new FilterException('Cannot resolve Meerkat filter variable ' . $filterParamMapping . ' in filter ' . $filterName);
                            }
                        } else {
                            $parameters[$this->filterRequiredParamMapping[$filterName]] = $filterParamMapping;
                        }
                    }
                }
            } else {
                throw new FilterException($filterName . ' Meerkat filter not found.');
            }
        }

        if (array_key_exists($filterName, $this->filters)) {
            /** @var CommentFilter $filter */
            $filter = $this->filters[$filterName];
            $filter->setContext($context);
            $filter->setParameters($parameters);
            $filter->setUser($this->getUser());

            $filterTags = $filter->getSupportedTags();

            if (count($filterTags) > 0) {
                if (in_array($tagContext, $filterTags) == false) {
                    throw new FilterException($filterName . ' is not supported by ' . $tagContext);
                }
            }

            return $filter->runFilter($comments);
        }

        throw new FilterException($filterName . ' Meerkat filter not found.');
    }

    /**
     * Checks if a filter exists.
     *
     * @param string $filterName The filter name.
     * @return bool
     * @throws FilterException
     */
    public function hasFilter($filterName)
    {
        $originalName = $filterName;

        if (Str::contains($filterName, '(')) {
            $filterParts = explode('(', $filterName);

            if (count($filterParts) == 2) {
                $filterName = trim($filterParts[0]);
                $filterParamMapping = trim($filterParts[1]);

                if (Str::endsWith($filterParamMapping, ')') === false) {
                    throw new FilterException('Unmatched "(" in: ' . $originalName);
                }

                $filterParamMapping = mb_substr($filterParamMapping, 0, -1);

                $hasFilter = array_key_exists($filterName, $this->filters);

                if ($hasFilter) {
                    $this->paramMapping[$filterName] = $filterParamMapping;
                }

                return $hasFilter;
            } else {
                return false;
            }
        }

        return array_key_exists($filterName, $this->filters);
    }

    /**
     * Gets the currently used Statamic user, if any.
     *
     * @return mixed|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * The current Statamic user.
     * @param mixed|null $statamicUser
     */
    public function setUser(AuthorContract $statamicUser)
    {
        $this->user = $statamicUser;
    }

}
