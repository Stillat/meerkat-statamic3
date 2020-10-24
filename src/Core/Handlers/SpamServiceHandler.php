<?php

namespace Stillat\Meerkat\Core\Handlers;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Exceptions\FilterException;
use Stillat\Meerkat\Core\Guard\SpamChecker;
use Stillat\Meerkat\Core\GuardConfiguration;

/**
 * Class SpamServiceHandler
 *
 * Checks submissions for spam.
 *
 * @package Stillat\Meerkat\Core\Handlers
 * @since 2.0.0
 */
class SpamServiceHandler extends BaseHandler
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
                if ($this->config->autoDeleteSpam === true) {
                    $result = $this->storageManager->removeById($comment->getId());

                    if ($result->success === false) {
                        $this->storageManager->setIsSpamById($comment->getId());
                    }
                } else {
                    $this->storageManager->setIsSpamById($comment->getId());
                }
            }
        } else {
            if ($this->config->unpublishOnGuardFailures === true) {
                $this->storageManager->setIsNotApprovedById($comment->getId());
            }
        }
    }


    /**
     * @param CommentContract $comment The comment to check.
     * @throws FilterException
     */
    public function handle(CommentContract $comment)
    {
        $this->checkForSpam($comment);
    }

}
