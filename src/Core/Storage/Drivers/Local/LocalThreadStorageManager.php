<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local;

use DirectoryIterator;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Parsing\YAMLParserContract;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Logging\ErrorLog;
use Stillat\Meerkat\Core\Logging\LocalErrorCodeRepository;
use Stillat\Meerkat\Core\Paths\PathUtilities;
use Stillat\Meerkat\Core\Storage\Drivers\Local\Conversions\ThreadSoftDeleteConverter;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Support\Str;
use Stillat\Meerkat\Core\Threads\Thread;
use Stillat\Meerkat\Core\Threads\ThreadMetaData;
use Stillat\Meerkat\Core\ValidationResult;
use Stillat\Meerkat\Core\Validators\PathPrivilegeValidator;

/**
 * Class LocalThreadStorageManager
 *
 * Provides the necessary functionality to use a local
 * system directory to store the Meerkat threads.
 *
 * @package Stillat\Meerkat\Core\Storage\Drivers\Local
 * @since 2.0.0
 */
class LocalThreadStorageManager implements ThreadStorageManagerContract
{

    /**
     * The file extension used to store a thread's meta data.
     */
    const EXT_THREAD_META = '.meta';

    /**
     * A collection of previously resolved thread meta-data.
     *
     * Memoization for the following:
     *  - getMetaData()
     *
     * @var array
     */
    protected static $metaResolverCache = [];

    /**
     * The Meerkat Core configuration container.
     *
     * @var Configuration
     */
    private $meerkatConfiguration;

    /**
     * The local system path where comments are stored.
     *
     * @var string|string[]
     */
    private $storagePath;

    /**
     * Indicates if the configured storage directory was validated.
     *
     * @var bool
     */
    private $directoryValidated = false;

    /**
     * Indicates if the configured storage directory is usable.
     *
     * @var bool
     */
    private $canUseDirectory = false;

    /**
     * The YAMLParser implementation instance.
     *
     * @var YAMLParserContract
     */
    private $yamlParser;

    /**
     * The context resolver implementation instance.
     *
     * @var ContextResolverContract The instance.
     */
    private $contextResolver;

    /**
     * A collection of storage directory validation results.
     *
     * @var ValidationResult
     */
    private $validationResults;

    public function __construct(Configuration $config, YAMLParserContract $yamlParser, ContextResolverContract $contextResolver)
    {
        $this->meerkatConfiguration = $config;
        $this->yamlParser = $yamlParser;
        $this->contextResolver = $contextResolver;

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

        $results = PathPrivilegeValidator::validatePathPermissions(
            $this->storagePath,
            Errors::DRIVER_LOCAL_INSUFFICIENT_PRIVILEGES
        );

        $this->validationResults = $results[PathPrivilegeValidator::RESULT_VALIDATION_RESULTS];
        $this->canUseDirectory = $results[PathPrivilegeValidator::RESULT_CAN_USE_DIRECTORY];

        $this->validationResults->updateValidity();
        $this->directoryValidated = true;

        return $this->validationResults;
    }

    /**
     * Attempts to retrieve all comment threads.
     *
     * @param false $withTrashed Whether to include soft deleted threads.
     * @return ThreadContract[]
     */
    public function getAllThreads($withTrashed = false)
    {
        if ($this->canUseDirectory === false) {
            return [];
        }

        $threadCandidates = $this->getAllThreadIds($withTrashed);
        $threadsToReturn = [];
        $threadIdsToUse = [];

        if ($withTrashed == false) {
            foreach ($threadCandidates as $threadId) {
                $metaData = $this->getMetaData($threadId);

                if ($metaData !== null && $metaData->getIsTrashed() === false) {
                    $threadIdsToUse[] = $threadId;
                }
            }
        } else {
            $threadIdsToUse = $threadCandidates;
        }

        $threadCandidates = null;

        if (count($threadIdsToUse) == 0) {
            return $threadsToReturn;
        }

        foreach ($threadIdsToUse as $threadId) {
            $newThread = $this->materializeThread($threadId, false);

            $threadsToReturn[] = $newThread;
        }

        return $threadsToReturn;
    }

    /**
     * Returns the identifiers of all currently stored threads.
     *
     * @param bool $includeTrashed Indicates if soft-deleted threads should be included.
     * @return array
     */
    public function getAllThreadIds($includeTrashed = false)
    {
        // Note: This method should handle any automatic data structure
        //       changes without requiring the user to do any work.

        if ($this->canUseDirectory === false) {
            return [];
        }

        $candidates = [];

        if (is_dir($this->storagePath)) {
            $dirIterator = new DirectoryIterator($this->storagePath);

            foreach ($dirIterator as $fileInfo) {
                if ($fileInfo->isDot() || $fileInfo->isFile()) {
                    continue;
                }

                $pathName = $fileInfo->getFilename();
                $candidateName = $pathName;
                $didConvert = false;

                if ($this->isValidThreadId($pathName)) {
                    if (mb_strlen($pathName) === 37) {
                        ThreadSoftDeleteConverter::convert($this, $fileInfo->getPath(), $pathName);

                        // Clean up the name we will return.
                        $candidateName = ltrim($pathName, ThreadContract::LEGACY_SOFT_DELETE_PREFIX);
                        $didConvert = true;
                    }

                    $candidates[] = $candidateName;

                    // Create any meta data that may be missing automatically.
                    if ($didConvert == false) {
                        if ($this->hasMetaData($candidateName) == false) {
                            $metaData = $this->createMetaFromExistingThread($candidateName);

                            if ($metaData !== null) {
                                $this->saveMetaData($candidateName, $metaData);
                            }
                        }
                    }
                }
            }
        }

        return $candidates;
    }

    /**
     * Performs a simple check to determine if the root thread path is a valid ID.
     *
     * @param string $threadPath The basic path to check.
     *
     * @return bool
     */
    protected function isValidThreadId($threadPath)
    {
        // Length 36 is a normal GUID.
        $threadIdLength = mb_strlen($threadPath);

        if ($threadIdLength == 36) {
            return true;
        }

        if ($threadIdLength == 37 && Str::startsWith($threadPath, ThreadContract::LEGACY_SOFT_DELETE_PREFIX)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a thread has existing meta data.
     *
     * @param string $contextId The thread's string identifier.
     * @return bool
     */
    private function hasMetaData($contextId)
    {
        if ($this->canUseDirectory === false) {
            return false;
        }

        $targetPath = $this->storagePath . Paths::SYM_FORWARD_SEPARATOR .
            $contextId . Paths::SYM_FORWARD_SEPARATOR . self::EXT_THREAD_META;

        return file_exists($targetPath);
    }

    /**
     * Creates a new thread meta data object for the provided thread.
     *
     * @param string $contextId The thread's string identifier.
     * @return ThreadMetaData|null
     */
    private function createMetaFromExistingThread($contextId)
    {
        if ($this->canUseDirectory === false) {
            return null;
        }

        $metaData = new ThreadMetaData();
        $metaData->setCreatedOn(time());
        $metaData->setIsTrashed(false);

        $threadPath = $this->storagePath . Paths::SYM_FORWARD_SEPARATOR . $contextId;

        if (file_exists($threadPath) && is_dir($threadPath)) {
            $makeTime = filectime($threadPath);

            if ($makeTime < $metaData->getCreatedUtc()) {
                $metaData->setCreatedOn($makeTime);
            }
        } else {
            LocalErrorCodeRepository::log(ErrorLog::warning(
               Errors::THREAD_META_REQUESTED_FOR_MISSING_THREAD,
               $contextId
            ));
        }

        return $metaData;
    }

    /**
     * Attempts to save the provided meta data with for the thread.
     *
     * @param string $contextId The thread's string identifier.
     * @param ThreadMetaData $data
     * @return bool
     */
    private function saveMetaData($contextId, ThreadMetaData $data)
    {
        if ($this->canUseDirectory === false) {
            return false;
        }

        $targetPath = $this->storagePath . Paths::SYM_FORWARD_SEPARATOR .
            $contextId . Paths::SYM_FORWARD_SEPARATOR . self::EXT_THREAD_META;

        $wasSuccess = file_put_contents($targetPath, $this->yamlParser->toYaml($data->toArray()));

        if ($wasSuccess == false) {
            LocalErrorCodeRepository::log(ErrorLog::make(
               Errors::THREAD_META_DATA_COULD_NOT_BE_SAVED,
                $contextId
            ));
        }

        return $wasSuccess;
    }

    /**
     * Gets any existing meta data for the provided thread.
     *
     * @param string $contextId The thread's identifier.
     * @return ThreadMetaData|null
     */
    private function getMetaData($contextId)
    {
        if ($this->canUseDirectory === false) {
            return null;
        }

        if (array_key_exists($contextId, self::$metaResolverCache)) {
            return self::$metaResolverCache[$contextId];
        }

        $targetPath = $this->storagePath . Paths::SYM_FORWARD_SEPARATOR .
            $contextId . Paths::SYM_FORWARD_SEPARATOR . self::EXT_THREAD_META;

        if (file_exists($targetPath)) {
            $dataArray = $this->yamlParser->parseDocument(file_get_contents($targetPath));

            if ($dataArray !== null && is_array($dataArray)) {
                $metaData = ThreadMetaData::makeFromArray($dataArray);

                self::$metaResolverCache[$contextId] = $metaData;

                return $metaData;
            }
        }

        return null;
    }

    /**
     * Creates a new instance of Thread for the provided identifier.
     *
     * @param string $threadId The thread's identifier.
     * @param bool $includeComments Indicates if comments should be included.
     * @return Thread|null
     */
    private function materializeThread($threadId, $includeComments)
    {
        if ($this->canUseDirectory === false) {
            return null;
        }

        $newThread = new Thread();

        $newThread->setMetaData($this->getMetaData($threadId));
        $newThread->setId($threadId);
        $newThread->setContextId($threadId);

        $threadContext = $this->contextResolver->findById($threadId);

        if ($threadContext !== null) {
            $newThread->setContext($threadContext);
            $newThread->setIsUsable(true);
        } else {
            $newThread->setIsUsable(false);
            LocalErrorCodeRepository::log(ErrorLog::warning(
                Errors::THREAD_CONTEXT_NOT_FOUND,
                $threadId
            ));
        }

        if ($includeComments) {
            $newThread->setComments($this->getAllComments($newThread));
        }

        return $newThread;
    }

    /**
     * Retrieves the comments for the provided thread.
     *
     * @param ThreadContract $thread
     * @return CommentContract[]
     */
    public function getAllComments(ThreadContract $thread)
    {
        return $this->getAllCommentsById($thread->getId());
    }

    /**
     * Gets all the comments for the provided thread identifier.
     *
     * @param string $threadId The thread's string identifier.
     * @return CommentContract[]
     */
    public function getAllCommentsById($threadId)
    {
        if ($this->canUseDirectory === false) {
            return [];
        }

        return [];
    }

    /**
     * Attempts the provided thread.
     *
     * @param ThreadContract $thread The thread instance.
     * @return bool
     */
    public function save(ThreadContract $thread)
    {
        if ($this->canUseDirectory === false) {
            return false;
        }

        return $this->saveMetaData($thread->getId(), $thread->getMetaData());
    }

    /**
     * Attempts to create a new thread for the provided context.
     *
     * @param ThreadContextContract $context The thread's context.
     * @return bool
     */
    public function createForContext(ThreadContextContract $context)
    {
        if ($this->canUseDirectory === false) {
            return false;
        }

        if ($this->isValidThreadId($context->getId())) {
            return false;
        }

        if ($this->existsForContext($context->getId(), true)) {
            return true;
        }

        $path = $this->determineVirtualPathById($context->getId());
        $threadMetaData = new ThreadMetaData();
        $threadMetaData->setCreatedOn(time());
        $threadMetaData->setIsTrashed(false);

        $threadDirectoryCreated = mkdir($path, Paths::DIRECTORY_PERMISSIONS, true);

        if ($threadDirectoryCreated) {
            return $this->saveMetaData($context->getId(), $threadMetaData);
        }

        return false;
    }

    /**
     * Returns a value indicating if a thread exists with the provided identifier.
     *
     * @param string $contextId The thread's identifier.
     * @param bool $withTrashed Indicates if soft-deleted threads are considered.
     * @return bool
     */
    public function existsForContext($contextId, $withTrashed)
    {
        if ($this->canUseDirectory === false) {
            return false;
        }

        $targetPath = $this->storagePath . Paths::SYM_FORWARD_SEPARATOR . $contextId;

        if (file_exists($targetPath) == false || is_dir($targetPath) == false) {
            return false;
        }

        if ($withTrashed == true) {
            return true;
        }

        $metaData = $this->getMetaData($contextId);

        if ($metaData !== null && $metaData->getIsTrashed()) {
            return false;
        }

        return true;
    }

    /**
     * Generates an internal path for the given thread identifier.
     *
     * @param string $id The thread's identifier.
     * @return string
     */
    public function determineVirtualPathById($id)
    {
        return $this->storagePath . Paths::SYM_FORWARD_SEPARATOR . $id;
    }

    /**
     * Attempts to permanently delete the provided thread instance.
     *
     * @param ThreadContract $thread The thread instance.
     * @return bool
     */
    public function delete(ThreadContract $thread)
    {
        if ($this->canUseDirectory === false) {
            return false;
        }

        $wasDeleted = $this->deleteById($thread->getId());

        if ($wasDeleted) {
            $thread->setIsUsable(false);
        }

        return $wasDeleted;
    }

    /**
     * Attempts to remove a thread based on its identifier.
     *
     * @param string $id The thread's identifier.
     * @return bool
     */
    public function deleteById($id)
    {
        return $this->removeById($id);
    }

    /**
     * Attempts to permanently remove a thread by its identifier.
     *
     * @param string $id The thread's identifier.
     * @return bool
     */
    public function removeById($id)
    {
        if ($this->canUseDirectory === false) {
            return false;
        }

        // Danger Zone: This method permanently deletes things.
        $targetPath = $this->storagePath . Paths::SYM_FORWARD_SEPARATOR . $id;

        if (file_exists($targetPath) === false || is_dir($targetPath) === false) {
            // Cannot delete a directory that isn't there.
            return false;
        }

        $this->recursivelyRemoveDirectory($targetPath);

        $stillExists = file_exists($targetPath);

        if ($stillExists) {
            return false;
        }

        return true;
    }

    /**
     * Recursively removes the contents of a directory.
     *
     * @param string $directory The path to remove.
     */
    private function recursivelyRemoveDirectory($directory)
    {
        if (is_dir($directory)) {
            $objects = scandir($directory);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($directory . Paths::SYM_FORWARD_SEPARATOR . $object) == 'dir') {
                        $this->recursivelyRemoveDirectory($directory . Paths::SYM_FORWARD_SEPARATOR . $object);
                    } else {
                        unlink($directory . Paths::SYM_FORWARD_SEPARATOR . $object);
                    }
                }
            }
            reset($objects);
            rmdir($directory);
        }
    }

    /**
     * Attempts to soft-delete the provided thread instance.
     *
     * @param ThreadContract $thread The thread instance.
     * @return bool
     */
    public function softDelete(ThreadContract $thread)
    {
        $wasSoftDeleted = $this->softDeleteById($thread->getId());

        $thread->setIsTrashed(true);

        return $wasSoftDeleted;
    }

    /**
     * Attempts to soft-delete a thread by its identifier.
     * s
     * @param string $id The thread's identifier.
     * @return bool
     */
    public function softDeleteById($id)
    {
        if ($this->canUseDirectory === false) {
            return false;
        }

        if ($this->existsForContext($id, false)) {
            $metaData = new ThreadMetaData();
            $metaData->setIsTrashed(true);

            return $this->updateMetaData($id, $metaData);
        }

        return false;
    }

    /**
     * Updates the meta data for the provided thread.
     *
     * @param string $contextId The thread's string identifier.
     * @param ThreadMetaData $metaData
     * @return bool
     */
    public function updateMetaData($contextId, ThreadMetaData $metaData)
    {
        if ($this->canUseDirectory === false) {
            return false;
        }

        $currentMetaData = null;

        if ($this->hasMetaData($contextId)) {
            $currentMetaData = $this->getMetaData($contextId);
        } else {
            $currentMetaData = $this->createMetaFromExistingThread($contextId);
        }

        if ($currentMetaData !== null) {
            $currentMetaData->update($metaData);
        } else {
            $metaData->setCreatedOn(time());
            $metaData->setIsTrashed(false);
            $currentMetaData = $metaData;
        }

        $thread = $this->findById($contextId);

        $thread->setMetaData($currentMetaData);

        return $this->saveMetaData($contextId, $currentMetaData);
    }

    /**
     * Attempts to locate a thread by its identifier.
     *
     * @param string $id The thread's identifier.
     * @param bool $withTrashed Indicates if soft-deleted threads should be considered.
     * @param bool $includeComments Indicates if comments should be included with the thread.
     * @return Thread|null
     */
    public function findById($id, $withTrashed = false, $includeComments = true)
    {
        if ($this->canUseDirectory === false) {
            return null;
        }

        if ($this->existsForContext($id, $withTrashed) == false) {
            return null;
        }

        return $this->materializeThread($id, $includeComments);
    }

    /**
     * Attempts to move comments from one thread to another thread.
     *
     * @param string $sourceThreadId The identifier of the thread to move data from.
     * @param string $targetThreadId The identifier of the thread to move data to.
     * @return bool
     */
    public function moveThread($sourceThreadId, $targetThreadId)
    {
        if ($this->canUseDirectory === false) {
            return false;
        }

        if ($this->existsForContext($sourceThreadId, true) === false) {
            return false;
        }

        $sourcePath = $this->determineVirtualPathById($sourceThreadId);
        $targetPath = $this->determineVirtualPathById($targetThreadId);

        $this->recursivelyCopyDirectory($sourcePath, $targetPath, true);

        if (file_exists($sourcePath)) {
            return false;
        }

        return true;
    }

    /**
     * Attempts to copy all the source directories contents to the destination directory.
     *
     * @param string $source The path that things should be copied from.
     * @param string $destination The path that things should be copied to.
     * @param bool $cleanUpSource Whether to remove all the contents from the source directory.
     */
    private function recursivelyCopyDirectory($source, $destination, $cleanUpSource)
    {
        if (file_exists($destination) == false) {
            mkdir($destination, Paths::DIRECTORY_PERMISSIONS, true);
        }

        if (is_dir($source)) {
            $dirHandle = opendir($source);

            while (false !== ($file = readdir($dirHandle))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($source . Paths::SYM_FORWARD_SEPARATOR . $file)) {
                        $this->recursivelyCopyDirectory(
                            $source . Paths::SYM_FORWARD_SEPARATOR . $file,
                            $destination . Paths::SYM_FORWARD_SEPARATOR . $file,
                            false
                        );
                    } else {
                        copy(
                            $source . Paths::SYM_FORWARD_SEPARATOR . $file,
                            $destination . Paths::SYM_FORWARD_SEPARATOR . $file
                        );
                    }
                }
            }

            closedir($dirHandle);
        }

        if ($cleanUpSource) {
            $this->recursivelyRemoveDirectory($source);
        }
    }

    /**
     * Attempts to restore a thread by it's identifier.
     *
     * The thread to restore should:
     *   1) Exist
     *   2) Previously been soft-deleted.
     *
     * @param string $threadId The thread's identifier.
     * @return bool
     */
    public function restoreThread($threadId)
    {
        if ($this->canUseDirectory === false) {
            return false;
        }

        if ($this->existsForContext($threadId, true)) {
            $metaData = new ThreadMetaData();
            $metaData->setIsTrashed(false);

            return $this->updateMetaData($threadId, $metaData);
        }

        return false;
    }
}