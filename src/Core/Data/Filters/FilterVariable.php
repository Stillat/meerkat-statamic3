<?php

namespace Stillat\Meerkat\Core\Data\Filters;

class FilterVariable
{
    /**
     * A collection of parameters supplied to the variable.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The variable's context, if any.
     *
     * @var mixed|null
     */
    protected $context = null;

    /**
     * The variable callback.
     *
     * @var callable|null
     */
    protected $variableCallback = null;

    /**
     * The variables's name, if any.
     *
     * @var string
     */
    protected $variableName = '';

    /**
     * The current User context.
     *
     * @var mixed|null
     */
    protected $user = null;

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
     * Sets the name of the variable.
     *
     * @param string $name The variables's name.
     */
    public function setName($name)
    {
        $this->variableName = $name;
    }

    /**
     * Gets the name of the variable.
     *
     * @return string
     */
    public function getName()
    {
        return $this->variableName;
    }

    /**
     * Gets the variable context, if available.
     *
     * @return mixed|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Sets the variables's context.
     *
     * @param mixed|null $context The context.
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Indicates the variable has a context.
     *
     * @return bool
     */
    public function hasContext()
    {
        return $this->context != null;
    }

    /**
     * Gets the variable's parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Sets the variable's parameters.
     *
     * @param array $parameters The parameters.
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
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
     * Sets the variable callable.
     *
     * @param callable $callable The variable to resolve.
     */
    public function setCallback($callable)
    {
        $this->variableCallback = \Closure::bind($callable, $this, get_class());
    }

    /**
     * Gets the variable callback.
     *
     * @return callable|null
     */
    public function getCallback()
    {
        return $this->variableCallback;
    }

    /**
     * Gets the resolvable value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return call_user_func($this->variableCallback);
    }

}
