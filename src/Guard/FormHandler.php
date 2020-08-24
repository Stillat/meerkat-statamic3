<?php

namespace Stillat\Meerkat\Guard;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Guard\SpamService;
use Stillat\Meerkat\Core\GuardConfiguration;

/**
 * Class FormHandler
 *
 * Executed after a comment is saved or updated.
 *
 * @package Stillat\Meerkat\Guard
 * @since 2.0.0
 */
class FormHandler
{

    /**
     * The Meerkat Core guard configuration container.
     *
     * @var GuardConfiguration
     */
    protected $config = null;

    /**
     * The SpamService instance.
     *
     * @var SpamService
     */
    protected $spamService = null;

    /**
     * The CommentStorageManagerContract implementation instance.
     *
     * @var CommentStorageManagerContract
     */
    protected $storageManager = null;

    public function __construct(GuardConfiguration $config, SpamService $service, CommentStorageManagerContract $commentManager)
    {
        $this->config = $config;
        $this->spamService = $service;
        $this->storageManager = $commentManager;
    }

    /**
     * Checks the provided comment is spam or not.
     *
     * @param CommentContract $comment The comment to test.
     */
    public function checkForSpam(CommentContract $comment)
    {
        $isSpam = $this->spamService->isSpam($comment);

        if ($this->spamService->hasErrors() === false) {
            if ($isSpam === false) {
                $this->storageManager->setIsHamById($comment->getId());
            } else {
                $this->storageManager->setIsSpamById($comment->getId());
            }
        } else {
            if ($this->config->unpublishOnGuardFailures === true) {
                $this->storageManager->setIsNotApprovedById($comment->getId());
            }
        }
    }

}
