<?php

namespace Stillat\Meerkat\Core\Threads;

use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;
use Stillat\Meerkat\Core\DataObject;

/**
 * Class Context
 *
 * Represents a post/page/etc in the host system.
 *
 * @package Stillat\Meerkat\Core\Threads
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
