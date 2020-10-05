<?php

namespace Stillat\Meerkat\Support\Facades\StaticHelpers;

use Illuminate\Support\Facades\Event;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Core\Contracts\Comments\CommentMutationPipelineContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardPipelineContract;
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
        self::listenToEvent(ControlPanelServiceProvider::EVENT_REGISTERING_CONTROL_PANEL, $handler);
    }

    protected static function listenToEvent($event, $handler)
    {
        Event::listen(Addon::ADDON_NAME . '.' . $event, $handler);
    }

    /**
     * Fired when a single comment is being constructed from disk.
     *
     * @param callable $handler The callback.
     */
    public static function onCollectingComment(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_COLLECTION, $handler);
    }

    /**
     * Fired when a collection of comments are being constructed from disk.
     *
     * @param callable $handler The callback.
     */
    public static function onCollectingAllComments(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_COLLECTION_ALL, $handler);
    }

    public static function onCommentCreating(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_CREATING, $handler);
    }

    public static function onCommentUpdating(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_EDITED, $handler);
    }

    public static function onCommentCreated(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_CREATED, $handler);
    }


    public static function onCommentRemoving(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_REMOVING, $handler);
    }

    public static function onCommentRemoved(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_REMOVED, $handler);
    }

    public static function onCommentSoftDeleted(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_SOFT_DELETED, $handler);
    }

    public static function onCommentReplying(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_REPLYING, $handler);
    }

    public static function onCommentReplied(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_REPLYING, $handler);
    }

    public static function onCommentMarkingAsSpam(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_MARKING_AS_SPAM, $handler);
    }

    public static function onCommentMarkedAsSpam(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_MARKED_AS_SPAM, $handler);
    }

    public static function onCommentMarkingAsHam(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_MARKING_AS_HAM, $handler);
    }

    public static function onCommentMarkedAsHam(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_MARKED_AS_HAM, $handler);
    }

    public static function onCommentApproving(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_APPROVING, $handler);
    }

    public static function onCommentApproved(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_APPROVED, $handler);
    }

    public static function onCommentUnapproving(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_UNAPPROVING, $handler);
    }

    public static function onCommentUnapproved(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_UNAPPROVED, $handler);
    }

    public static function onCommentRestoring(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_RESTORING, $handler);
    }

    public static function onCommentRestored(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_RESTORED, $handler);
    }


    public static function onGuardStarting(callable $handler)
    {
        self::listenToEvent(SpamGuardPipelineContract::MUTATION_REGISTERING, $handler);
    }

}
