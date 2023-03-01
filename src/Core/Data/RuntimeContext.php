<?php

namespace Stillat\Meerkat\Core\Data;

/**
 * Class RuntimeContext
 *
 * Represents a runtime context.
 *
 * @since 2.0.0
 */
class RuntimeContext
{
    /**
     * The run time contextual parameters, if any.
     *
     * @var array
     */
    public $parameters = [];

    /**
     * The run time context, if any.
     *
     * @var mixed|null
     */
    public $context = null;

    /**
     * The run time template tag context, if any.
     *
     * @var string
     */
    public $templateTagContext = '';
}
