<?php

namespace Stillat\Meerkat\Core\Contracts\Comments;

use Stillat\Meerkat\Core\Contracts\MutationPipelineContract;

/**
 * Interface CommentMutationPipelineContract
 *
 * Responsible for responding to comment data mutation requests
 *
 * @package Stillat\Meerkat\Core\Contracts\Comments
 * @since 2.0.0
 */
interface CommentMutationPipelineContract extends MutationPipelineContract
{
    /**
     * Identifies a request for when a Comment is manifesting.
     */
    const MUTATION_COLLECTION = 'comments.collecting';

    /**
     * Identifies a request to remove a comment.
     */
    const MUTATION_REMOVING = 'comments.beforeRemove';
    const MUTATION_REMOVED = 'comments.removed';

    const MUTATION_CREATING = 'comments.creating';
    const MUTATION_CREATED = 'comments.created';
    const MUTATION_EDITING = 'comments.editing';
    const MUTATION_EDITED = 'comments.edited';
    const MUTATION_REPLYING = 'comments.replying';
    const MUTATION_REPLIED = 'comments.replied';
    const MUTATION_MARKING_AS_SPAM = 'comments.spam.markingAsSpam';
    const MUTATION_MARKED_AS_SPAM = 'comments.spam.markedAsSpam';
    const MUTATION_MARKING_AS_HAM = 'comments.spam.markingAsHam';
    const MUTATION_MARKED_AS_HAM = 'comments.spam.markedAsHam';
    const MUTATION_APPROVING = 'comments.approving';
    const MUTATION_APPROVED = 'comments.approved';
    const MUTATION_UNAPPROVING = 'comments.unapproving';
    const MUTATION_UNAPPROVED = 'comments.unapproved';

}
