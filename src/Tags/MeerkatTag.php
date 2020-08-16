<?php

namespace Stillat\Meerkat\Tags;

use Statamic\Tags\Parameters;
use Statamic\Tags\Tags;

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

    public function hasParameterValue($key)
    {

        if ($this->params instanceof Parameters) {
            return $this->params->has($key);
        }

        return array_key_exists($key, $this->params);
    }


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

    public function getParameterArray()
    {
        if ($this->params instanceof  Parameters) {
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


}

