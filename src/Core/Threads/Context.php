<?php

namespace Stillat\Meerkat\Core\Threads;

use Stillat\Meerkat\Core\DataObject;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;

/**
 * Represents a post/page/etc in the host system
 *
 * @since 2.0.0
 */
class Context implements ThreadContextContract
{
    use DataObject;

    /**
     * The string identifier for the context, if available.
     *
     * @var string
     */
    public $contextId = '';

    /**
     * The name of the context, if available.
     *
     * @var string
     */
    public $contextName = '';

    /**
     * Returns the identifier string of the context.
     *
     * @return string
     */
    public function getId()
    {
        return $this->contextId;
    }

    /**
     * Returns the context's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->contextName;
    }
}
