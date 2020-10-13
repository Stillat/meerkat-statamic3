<?php

namespace Stillat\Meerkat\Core\Guard;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\GuardConfiguration;

/**
 * Class SubmitModeratorResultsHandler
 *
 * Checks the site configuration and submits false positive/negative spam results to third-party providers.
 *
 * @package Stillat\Meerkat\Core\Guard
 * @since 2.0.8
 */
class SubmitModeratorResultsHandler
{

    /**
     * The Meerkat Core GuardConfiguration instance.
     *
     * @var GuardConfiguration
     */
    private $config = null;

    /**
     * The SpamService instance.
     *
     * @var SpamService
     */
    private $spamService = null;

    public function __construct(GuardConfiguration $config, SpamService $service)
    {
        $this->config = $config;
        $this->spamService = $service;
    }

    /**
     * Asks the spam service submit the comment as either a false positive, or false negative.
     *
     * This method checks if the site has been configured to automatically submit specimens to third-parties.
     *
     * @param CommentContract $comment The comment to submit results for.
     * @param bool $isSpam Indicates if the comment is spam or not.
     */
    public function submitToProviders(CommentContract $comment, $isSpam)
    {
        if ($this->config->autoSubmitSpamToThirdParties === true) {
            if ($isSpam === true) {
                $this->spamService->submitAsSpam($comment);
            } else {
                $this->spamService->submitAsHam($comment);
            }
        }
    }

}
