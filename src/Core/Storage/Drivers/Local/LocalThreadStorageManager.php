<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local;

use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Paths\PathUtilities;
use Stillat\Meerkat\Core\ValidationResult;
use Stillat\Meerkat\Core\Validators\PathPrivilegeValidator;

class LocalThreadStorageManager implements  ThreadStorageManagerContract
{

    /**
     * The Meerkat Core configuration container.
     *
     * @var Configuration
     */
    private $meerkatConfiguration = null;

    private $storagePath = '';

    private $directoryValidated = false;

    private $canUseDirectory = false;

    /**
     * A collection of storage directory validation results.
     *
     * @var ValidationResult
     */
    private $validationResults = null;

    public function __construct(Configuration $config)
    {
        $this->meerkatConfiguration = $config;

        // Quick alias for less typing.
        $this->storagePath = PathUtilities::normalize($this->meerkatConfiguration->storageDirectory);

        $this->validationResults = new ValidationResult();
        $this->validate();
    }


    /**
     * Validates the storage driver configuration.
     *
     * @return ValidationResult
     */
    public function validate()
    {
        if ($this->directoryValidated) {
            return $this->validationResults;
        }

        $results = PathPrivilegeValidator::validatePathPermissions($this->storagePath, Errors::DRIVER_LOCAL_INSUFFICIENT_PRIVILEGES);

        $this->validationResults = $results[PathPrivilegeValidator::RESULT_VALIDATION_RESULTS];
        $this->canUseDirectory = $results[PathPrivilegeValidator::RESULT_CAN_USE_DIRECTORY];

        $this->validationResults->updateValidity();
        $this->directoryValidated = true;

        return $this->validationResults;
    }

    /**
     * @param false $withTrashed
     * @return ThreadContract[]
     */
    public function getAllThreads($withTrashed = false)
    {
        // TODO: Implement getAllThreads() method.
    }

    public function getAllThreadIds($includeTrashed = false)
    {
        if ($this->canUseDirectory === false) {
            return [];
        }

        dd('sdf;asdf', $this->meerkatConfiguration);
        // TODO: Implement getAllThreadIds() method.
    }

    public function existsForContext($contextId, $withTrashed)
    {
        // TODO: Implement existsForContext() method.
    }

    /**
     * @param ThreadContract $thread
     * @return CommentContract[]
     */
    public function getAllComments(ThreadContract $thread)
    {
        // TODO: Implement getAllComments() method.
    }

    /**
     * @param $threadId
     * @return CommentContract[]
     */
    public function getAllCommentsById($threadId)
    {
        // TODO: Implement getAllCommentsById() method.
    }

    public function findById($id, $withTrashed = false, $includeComments = true)
    {
        // TODO: Implement findById() method.
    }

    public function save(ThreadContract $thread)
    {
        // TODO: Implement save() method.
    }

    public function determineVirtualPathById($id)
    {
        // TODO: Implement determineVirtualPathById() method.
    }

    public function delete(ThreadContract $thread)
    {
        // TODO: Implement delete() method.
    }

    public function deleteById($id)
    {
        // TODO: Implement deleteById() method.
    }

    public function softDelete(ThreadContract $thread)
    {
        // TODO: Implement softDelete() method.
    }

    public function softDeleteById($id)
    {
        // TODO: Implement softDeleteById() method.
    }

    public function removeById($id)
    {
        // TODO: Implement removeById() method.
    }

    public function moveThread($sourceThreadId, $targetThreadId)
    {
        // TODO: Implement moveThread() method.
    }

    public function restoreThread($threadId)
    {
        // TODO: Implement restoreThread() method.
    }
}