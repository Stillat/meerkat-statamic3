<?php

namespace Stillat\Meerkat\Support\Facades\StaticHelpers;

use Illuminate\Support\Facades\Event;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Core\Contracts\Comments\CommentMutationPipelineContract;
use Stillat\Meerkat\Providers\ControlPanelServiceProvider;

/**
 * Trait ProvidesEventHelpers
 *
 * Provides helpers for interacting with Meerkat life-cycle events.
 *
 * @package Stillat\Meerkat\Support\Facades\StaticHelpers
 * @since 2.0.0
 */
trait ProvidesEventHelpers
{

    /**
     * Fired when Meerkat is registering itself with the Statamic Control Panel.
     *
     * @param callable $handler The callback.
     */
    public static function onRegisteringControlPanel(callable $handler)
    {
        Event::listen(Addon::ADDON_NAME . '.' . ControlPanelServiceProvider::EVENT_REGISTERING_CONTROL_PANEL, $handler);
    }

    /**
     * Fired when a single comment is being constructed from disk.
     *
     * @param callable $handler The callback.
     */
    public static function onCollectingComment(callable $handler)
    {
        Event::listen(Addon::ADDON_NAME . '.' . CommentMutationPipelineContract::MUTATION_COLLECTION, $handler);
    }

    /**
     * Fired when a collection of comments are being constructed from disk.
     *
     * @param callable $handler The callback.
     */
    public static function onCollectingAllComments(callable $handler)
    {
        Event::listen(Addon::ADDON_NAME . '.' . CommentMutationPipelineContract::MUTATION_COLLECTION_ALL, $handler);
    }

    // TODO: Provide helpers for the other life-cycle events :)

}
