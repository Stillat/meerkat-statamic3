<?php

namespace Stillat\Meerkat\Support\Facades\StaticHelpers;

use Illuminate\Support\Facades\Event;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Core\Contracts\Comments\CommentMutationPipelineContract;
use Stillat\Meerkat\Core\Contracts\Permissions\PermissionsMutationPipelineContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardPipelineContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadMutationPipelineContract;
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
     * Called when Meerkat is registering itself with the Statamic Control Panel.
     *
     * @param callable $handler The callback.
     */
    public static function onRegisteringControlPanel(callable $handler)
    {
        self::listenToEvent(ControlPanelServiceProvider::EVENT_REGISTERING_CONTROL_PANEL, $handler);
    }

    /**
     * Helper method to register Meerkat prefixed events.
     *
     * @param string $event The event suffix.
     * @param callable $handler The callback.
     */
    protected static function listenToEvent($event, $handler)
    {
        Event::listen(Addon::ADDON_NAME . '.' . $event, $handler);
    }

    /**
     * Called a single comment is being constructed from disk.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCollectingComment(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_COLLECTION, $handler);
    }

    /**
     * Called when a collection of comments are being constructed from disk.
     *
     * Callable arguments:
     *      0: CommentContract[]
     *
     * @param callable $handler The callback.
     */
    public static function onCollectingAllComments(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_COLLECTION_ALL, $handler);
    }

    /**
     * Called before a comment is fully created and saved.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentCreating(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_CREATING, $handler);
    }

    /**
     * Called when a comment is being updated, but before it is saved.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentUpdating(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_EDITED, $handler);
    }

    /**
     * Called after a comment has been created.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentCreated(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_CREATED, $handler);
    }

    /**
     * Called before a comment is removed.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentRemoving(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_REMOVING, $handler);
    }

    /**
     * Called after a comment is removed.
     *
     * Callable arguments:
     *      0: string - The comment identifier.
     *
     * @param callable $handler The callback.
     */
    public static function onCommentRemoved(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_REMOVED, $handler);
    }

    /**
     * Called after a comment has been soft-deleted.
     *
     * Callable arguments:
     *      0: string - The comment identifier.
     *
     * @param callable $handler The callback.
     */
    public static function onCommentSoftDeleted(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_SOFT_DELETED, $handler);
    }

    /**
     * Called before a reply is saved to disk.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentReplying(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_REPLYING, $handler);
    }

    /**
     * Called after a comment reply has been saved.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentReplied(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_REPLYING, $handler);
    }

    /**
     * Called before a comment is marked as spam.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentMarkingAsSpam(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_MARKING_AS_SPAM, $handler);
    }

    /**
     * Called before a comment is marked as not spam.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentMarkingAsHam(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_MARKING_AS_HAM, $handler);
    }

    /**
     * Called after a comment has been marked as spam or ham.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentSpamStatusUpdated(callable $handler)
    {
        self::onCommentMarkedAsSpam($handler);
        self::onCommentMarkedAsHam($handler);
    }

    /**
     * Called after a comment has been marked as spam.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentMarkedAsSpam(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_MARKED_AS_SPAM, $handler);
    }

    /**
     * Called after a comment has been marked as not spam.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentMarkedAsHam(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_MARKED_AS_HAM, $handler);
    }

    /**
     * Called before a comment is approved.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentApproving(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_APPROVING, $handler);
    }

    /**
     * Called after a comment has been approved.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentApproved(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_APPROVED, $handler);
    }

    /**
     * Called before a comment is un-approved.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentUnapproving(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_UNAPPROVING, $handler);
    }

    /**
     * Called after a comment is un-approved.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentUnapproved(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_UNAPPROVED, $handler);
    }

    /**
     * Called before a comment is restored from a soft-deleted state.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentRestoring(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_RESTORING, $handler);
    }

    /**
     * Called after a comment is restored from a soft-deleted state.
     *
     * Callable arguments:
     *      0: CommentContract
     *
     * @param callable $handler The callback.
     */
    public static function onCommentRestored(callable $handler)
    {
        self::listenToEvent(CommentMutationPipelineContract::MUTATION_RESTORED, $handler);
    }

    /**
     * Called after the SpamService has been instantiated and is ready.
     *
     * Callable arguments:
     *      0: SpamService
     *
     * @param callable $handler The callback.
     */
    public static function onGuardStarting(callable $handler)
    {
        self::listenToEvent(SpamGuardPipelineContract::MUTATION_REGISTERING, $handler);
    }

    /**
     * Called before a thread is removed.
     *
     * Callable arguments:
     *      0: ThreadRemovalEventArgs
     *
     * @param callable $handler The callback.
     */
    public static function onThreadRemoving(callable $handler)
    {
        self::listenToEvent(ThreadMutationPipelineContract::MUTATION_REMOVING, $handler);
    }

    /**
     * Called after a thread has been removed.
     *
     * Callable arguments:
     *      0: ThreadContextContract
     *
     * @param callable $handler The callback.
     */
    public static function onThreadRemoved(callable $handler)
    {
        self::listenToEvent(ThreadMutationPipelineContract::MUTATION_REMOVED, $handler);
    }

    /**
     * Called after a thread has been soft deleted.
     *
     * Callable arguments:
     *      0: ThreadContextContract
     *
     * @param callable $handler The callback.
     */
    public static function onThreadSoftDeleted(callable $handler)
    {
        self::listenToEvent(ThreadMutationPipelineContract::MUTATION_SOFT_DELETED, $handler);
    }

    /**
     * Called before a thread is created.
     *
     * Callable arguments:
     *      0: ThreadContextContract
     *
     * @param callable $handler The callback.
     */
    public static function onThreadCreating(callable $handler)
    {
        self::listenToEvent(ThreadMutationPipelineContract::MUTATION_CREATING, $handler);
    }

    /**
     * Called after a thread is created.
     *
     * Callback arguments:
     *      0: ThreadContextContract
     *
     * @param callable $handler The callback.
     */
    public static function onThreadCreated(callable $handler)
    {
        self::listenToEvent(ThreadMutationPipelineContract::MUTATION_CREATED, $handler);
    }

    /**
     * Called before a thread is moved to another context.
     *
     * Callable arguments:
     *      0: ThreadMovingEventArgs
     *
     * @param callable $handler The callback.
     */
    public static function onThreadMoving(callable $handler)
    {
        self::listenToEvent(ThreadMutationPipelineContract::MUTATION_MOVING, $handler);
    }

    /**
     * Called after a thread has been moved.
     *
     * Callable arguments:
     *      0: ThreadContextContract
     *
     * @param callable $handler The callback.
     */
    public static function onThreadMoved(callable $handler)
    {
        self::listenToEvent(ThreadMutationPipelineContract::MUTATION_MOVED, $handler);
    }

    /**
     * Called before a thread is restored from a soft-deleted state.
     *
     * Callable arguments:
     *      0: ThreadRestoringEventArgs
     *
     * @param callable $handler The callback.
     */
    public static function onThreadRestoring(callable $handler)
    {
        self::listenToEvent(ThreadMutationPipelineContract::MUTATION_RESTORING, $handler);
    }

    /**
     * Called after a thread has been restored from a soft-deleted state.
     *
     * Callable arguments:
     *      0: ThreadContextContract
     *
     * @param callable $handler The callback.
     */
    public static function onThreadRestored(callable $handler)
    {
        self::listenToEvent(ThreadMutationPipelineContract::MUTATION_RESTORED, $handler);
    }

    /**
     * Called when permissions are being resolved for an individual identity.
     *
     * Callable arguments:
     *      0: AuthorContract
     *      1: PermissionsSet
     *
     * @param callable $handler The callback.
     */
    public static function onPermissionsResolving(callable $handler)
    {
        self::listenToEvent(PermissionsMutationPipelineContract::MUTATION_RESOLVING, $handler);
    }

}
