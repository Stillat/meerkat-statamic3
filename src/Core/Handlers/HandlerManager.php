<?php

namespace Stillat\Meerkat\Core\Handlers;

use Exception;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class HandlerManager
 *
 * Provides features to register, manage, and run various comment handlers.
 *
 * @package Stillat\Meerkat\Core\Comments\Handlers
 * @since 2.0.0
 */
class HandlerManager
{

    /**
     * A collection of handlers.
     *
     * @var BaseHandler[]
     */
    protected $handlers = [];

    /**
     * Adds a new handler to the manager.
     *
     * @param string $name A friendly name for the handler.
     * @param BaseHandler $handler The handler to register.
     */
    public function registerHandler($name, $handler)
    {
       if (is_object($handler) && $handler instanceof BaseHandler) {
           $this->handlers[$name] = $handler;
       }
    }

    /**
     * Tests if a handler has been registered.
     *
     * @param string $name The friendly name of the handler.
     * @return bool
     */
    public function hasHandler($name)
    {
        return array_key_exists($name, $this->handlers);
    }

    /**
     * Removes a handler from the manager.
     *
     * @param string $name The friendly name of the handler.
     */
    public function removeHandler($name)
    {
        if ($this->hasHandler($name)) {
            unset($this->handlers[$name]);
        }
    }

    /**
     * Runs all registered handlers against the provided comment.
     *
     * @param CommentContract $comment The comment to run handlers against.
     */
    public function handle(CommentContract $comment)
    {
        // TODO: Capture handler errors.
        foreach ($this->handlers as $handlerName => $handler) {
            try {
                $handler->handle($comment);
            } catch (Exception $e) {
                // TODO: Log to error repository.
            }
        }
    }

}
