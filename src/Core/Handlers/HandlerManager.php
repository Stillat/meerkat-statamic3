<?php

namespace Stillat\Meerkat\Core\Handlers;

use Exception;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Logging\ExceptionLoggerFactory;
use Stillat\Meerkat\Core\Logging\LocalErrorCodeRepository;

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
     * Runs all registered handlers against the provided comment.
     *
     * @param CommentContract $comment The comment to run handlers against.
     */
    public function handle(CommentContract $comment)
    {
        foreach ($this->handlers as $handlerName => $handler) {
            try {
                $handler->handle($comment);
            } catch (Exception $e) {
                ExceptionLoggerFactory::log($e);
                LocalErrorCodeRepository::logCodeMessage(Errors::HANDLER_GENERAL_EXCEPTION, $e->getMessage());
            }
        }
    }

}
