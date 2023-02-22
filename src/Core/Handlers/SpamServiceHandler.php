<?php

namespace Stillat\Meerkat\Core\Handlers;

use Stillat\Meerkat\Core\Configuration;
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
 * @since 2.0.0
 */
class SpamServiceHandler extends BaseHandler
{
    /**
     * The Meerkat Core Configuration container.
     *
     * @var Configuration
     */
    protected $coreConfig = null;

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

    /**
     * The EmailHandler instance.
     *
     * @var EmailHandler
     */
    private $emailHandler = null;

    public function __construct(Configuration $configuration,
                                GuardConfiguration $config,
                                SpamChecker $spamChecker,
                                CommentStorageManagerContract $commentManager,
                                EmailHandler $mailHandler)
    {
        $this->coreConfig = $configuration;
        $this->config = $config;
        $this->spamChecker = $spamChecker;
        $this->storageManager = $commentManager;
        $this->emailHandler = $mailHandler;
    }

    /**
     * @param  CommentContract  $comment The comment to check.
     *
     * @throws FilterException
     */
    public function handle(CommentContract $comment)
    {
        $this->checkForSpam($comment);
    }

    /**
     * Checks the provided comment is spam or not.
     *
     * @param  CommentContract  $comment The comment to test.
     *
     * @throws FilterException
     */
    public function checkForSpam(CommentContract $comment)
    {
        $this->spamChecker->checkSingle($comment);

        $isSpam = $this->spamChecker->checkCommentsNow();

        if ($this->spamChecker->hasErrors() === false) {
            if ($isSpam === false) {
                $this->storageManager->setIsHamById($comment->getId());

                if ($this->coreConfig->sendEmails === true &&
                    ($this->coreConfig->onlySendEmailIfNotSpam === true || $this->config->autoDeleteSpam === true)) {
                    // The normal mail handler may have skipped sending an email until
                    // the spam service was able to fully check the submission.
                    $comment->setDataAttribute(CommentContract::KEY_HAS_CHECKED_FOR_SPAM, true);
                    $comment->setDataAttribute(CommentContract::KEY_SPAM, false);
                    $this->emailHandler->handle($comment);
                }
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
}
