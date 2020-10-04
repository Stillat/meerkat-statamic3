<?php

namespace Stillat\Meerkat\Core\Handlers;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Guard\SpamService;
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

    public function __construct(GuardConfiguration $config, SpamService $spamService, CommentStorageManagerContract $storageManager)
    {
        $this->config = $config;
        $this->spamService = $spamService;
        $this->storageManager = $storageManager;
    }

    public function handle(CommentContract $comment)
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
