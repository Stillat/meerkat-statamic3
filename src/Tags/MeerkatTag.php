<?php

namespace Stillat\Meerkat\Tags;

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
     * The tag's parameters.
     *
     * Used for compatibility with the HasParameters concern.
     *
     * @var array
     */
    protected $parameters = [];

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
        $this->parameters = $tags->params;
        $this->tag = $tags->tag;
        $this->method = $tags->method;
        $this->isPair = $tags->isPair;
        $this->parser = $tags->parser;
    }

    /**
     * Renders the tag content.
     *
     * @return string
     */
    abstract public function render();

}
