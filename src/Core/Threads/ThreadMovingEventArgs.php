<?php

namespace Stillat\Meerkat\Core\Threads;

use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\DataObject;

/**
 * Class ThreadMovingEventArgs
 *
 * Provided to listeners when a thread is being moved.
 *
 * @package Stillat\Meerkat\Core\Threads
 * @since 2.0.0
 */
class ThreadMovingEventArgs implements DataObjectContract
{
    use DataObject;

    /**
     * The source thread identifier.
     *
     * @var null|string
     */
    public $sourceThreadId = null;

    /**
     * The target thread identifier.
     *
     * @var null|string
     */
    public $targetThreadId = null;

    /**
     * The data attributes, if any.
     *
     * @var array
     */
    protected $attributes = [];
    /**
     * Indicates if Meerkat should proceed with the move.
     *
     * @var bool
     */
    protected $doMove = true;

    /**
     * Allows the move to proceed.
     *
     * @return ThreadMovingEventArgs
     */
    public function allowMove()
    {
        $this->doMove = true;

        return $this;
    }

    /**
     * Prevents the move from completing.
     *
     * @return ThreadMovingEventArgs
     */
    public function denyMove()
    {
        $this->doMove = false;

        return $this;
    }

    /**
     * Returns a value indicating if the move should proceed.
     *
     * @return bool
     */
    public function shouldMove()
    {
        return $this->doMove;
    }

}
