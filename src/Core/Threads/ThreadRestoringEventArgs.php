<?php

namespace Stillat\Meerkat\Core\Threads;

use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\DataObject;

class ThreadRestoringEventArgs implements DataObjectContract
{
    use DataObject;

    /**
     * The data attributes, if any.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Indicates if the restoration should continue.
     *
     * @var bool
     */
    protected $shouldRestore = true;

    /**
     * The thread's string identifier.
     *
     * @var null|string
     */
    public $threadId = null;

    /**
     * Requests that the restoration should continue.
     *
     * @return ThreadRestoringEventArgs
     */
    public function allowRestore()
    {
        $this->shouldRestore = true;

        return $this;
    }

    /**
     * Requests that the restoration should not continue.
     *
     * @return ThreadRestoringEventArgs
     */
    public function denyRestore()
    {
        $this->shouldRestore = false;

        return $this;
    }

    /**
     * Returns a value indicating if the restoration should continue.
     *
     * @return bool
     */
    public function shouldRestore()
    {
        return $this->shouldRestore;
    }
}
