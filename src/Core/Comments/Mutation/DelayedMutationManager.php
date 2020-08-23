<?php

namespace Stillat\Meerkat\Core\Comments\Mutation;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentMutationPipelineContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;

class DelayedMutationManager
{

    /**
     * The CommentMutationPipelineContract instance.
     *
     * @var CommentMutationPipelineContract
     */
    protected $mutationPipeline = null;

    protected $commentStorageManager = null;

    public function __construct(CommentMutationPipelineContract $mutationPipeline, CommentStorageManagerContract $storageManager)
    {
        $this->mutationPipeline = $mutationPipeline;
        $this->commentStorageManager = $storageManager;
    }

    public function raiseCreated($commentId)
    {
        $comment = $this->commentStorageManager->findById($commentId);

        if ($comment !== null && $comment instanceof CommentContract) {
            $this->mutationPipeline->created($comment, null);
        }
    }

    public function raiseUpdated($commentId)
    {
        $comment = $this->commentStorageManager->findById($commentId);

        if ($comment !== null && $comment instanceof CommentContract) {
            $this->mutationPipeline->updated($comment, null);
        }
    }

}