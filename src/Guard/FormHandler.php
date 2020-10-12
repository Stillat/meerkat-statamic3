<?php

namespace Stillat\Meerkat\Guard;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Guard\SpamChecker;
use Stillat\Meerkat\Core\Guard\SpamService;
use Stillat\Meerkat\Core\GuardConfiguration;

/**
 * Class FormHandler
 *
 * Executed after a comment is saved or updated.
 *
 * @package Stillat\Meerkat\Guard
 * @since 2.0.0
 * // TODO
 */

// TODO: Deprecated
class FormHandler
{

    /**
     * The Meerkat Core guard configuration container.
     *
     * @var GuardConfiguration
     */
    protected $config = null;

    /**
     * The SpamChecker instance.
     *
     * @var SpamChecker
     */
    protected $spamChecker = null;

    /**
     * The CommentStorageManagerContract implementation instance.
     *
     * @var CommentStorageManagerContract
     */
    protected $storageManager = null;

    public function __construct(GuardConfiguration $config, SpamChecker $spamChecker, CommentStorageManagerContract $commentManager)
    {
        $this->config = $config;
        $this->spamChecker = $spamChecker;
        $this->storageManager = $commentManager;
    }

    /**
     * Checks the provided comment is spam or not.
     *
     * @param CommentContract $comment The comment to test.
     * @throws FilterException
     */
    public function checkForSpam(CommentContract $comment)
    {
        $this->spamChecker->checkSingle($comment);

        $isSpam = $this->spamChecker->checkCommentsNow();

        if ($this->spamChecker->hasErrors() === false) {
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
