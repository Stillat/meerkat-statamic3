<?php

namespace Stillat\Meerkat\Core\Data\Filters;

use Closure;

/**
 * Class CommentFilter
 *
 * Represents a comment filter that can be applied to a collection of comments.
 *
 * @package Stillat\Meerkat\Core\Data\Filters
 * @since 1.5.85
 */
class CommentFilter
{
    const PARAM_GLOBAL_CURRENT = '*current*';

    /**
     * A collection of parameters supplied to the filter.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The filter's context, if any.
     *
     * @var mixed|null
     */
    protected $context = null;

    /**
     * The filter callback.
     *
     * @var callable|null
     */
    protected $filterCallback = null;

    /**
     * The filter's name, if any.
     *
     * @var string
     */
    protected $filterName = '';

    /**
     * The current User context.
     *
     * @var mixed|null
     */
    protected $user = null;

    /**
     * A collection of supported tags.
     *
     * @var array
     */
    protected $supportedTags = [];

    /**
     * Gets the user context.
     *
     * @return mixed|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the user context.
     *
     * @param mixed|null $user The user context.
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Indicates if the filter context has a user.
     *
     * @return bool
     */
    public function hasUser()
    {
        return $this->user != null;
    }

    /**
     * Gets the tags that this filter supports.
     *
     * @return array
     */
    public function getSupportedTags()
    {
        return $this->supportedTags;
    }

    /**
     * Sets the tags that this filter supports.
     *
     * @param array $tags The tags this filter supports.
     */
    public function setSupportedTags($tags)
    {
        $this->supportedTags = $tags;
    }

    /**
     * Sets the name of the filter.
     *
     * @param string $name The filter's name.
     */
    public function setName($name)
    {
        $this->filterName = $name;
    }

    /**
     * Gets the name of the filter.
     *
     * @return string
     */
    public function getName()
    {
        return $this->filterName;
    }

    /**
     * Gets the filter context, if available.
     *
     * @return mixed|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets the filter's context.
     *
     * @param mixed|null $context The context.
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Indicates the filter has a context.
     *
     * @return bool
     */
    public function hasContext()
    {
        return $this->context != null;
    }

    /**
     * Gets the filter's parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Sets the filter's parameters.
     *
     * @param array $parameters The parameters.
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Gets a parameter's value, or a default if it does not exist.
     *
     * @param string $key The parameter name to get.
     * @param null $default The default value to return.
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->parameters[$key];
        }

        return $default;
    }

    /**
     * Checks if a parameter with the given name exists.
     *
     * @param string $key The key to check.
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Sets the filter callable.
     *
     * @param callable $callable The function to run.
     */
    public function setCallback($callable)
    {
        $this->filterCallback = Closure::bind($callable, $this, get_class());
    }

    /**
     * Gets the filter callback.
     *
     * @return callable|null
     */
    public function getCallback()
    {
        return $this->filterCallback;
    }

    /**
     * Runs the filter against the provided comments.
     *
     * @param array|mixed $comments The comments.
     * @return mixed
     */
    public function runFilter($comments)
    {
        return call_user_func($this->filterCallback, $comments);
    }

}
