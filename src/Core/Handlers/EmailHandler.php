<?php

namespace Stillat\Meerkat\Core\Handlers;

use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\GuardConfiguration;
use Stillat\Meerkat\Core\Mail\Mailer;

/**
 * Class EmailHandler
 *
 * Handles the interaction between the mailer implementations and the underlying storage systems.
 *
 * @since 2.1.5
 */
class EmailHandler extends BaseHandler
{
    /**
     * The Core Configuration container.
     *
     * @var Configuration
     */
    protected $configuration = null;

    /**
     * The GuardConfiguration container.
     *
     * @var GuardConfiguration
     */
    protected $guardConfiguration = null;

    /**
     * The Mailer instance.
     *
     * @var Mailer
     */
    protected $mailer = null;

    public function __construct(Configuration $configuration, GuardConfiguration $guardConfiguration, Mailer $mailer)
    {
        $this->configuration = $configuration;
        $this->mailer = $mailer;
        $this->guardConfiguration = $guardConfiguration;
    }

    public function handle(CommentContract $comment)
    {
        if ($this->configuration->sendEmails === true &&
            is_array($this->configuration->addressToSendEmailTo) &&
            count($this->configuration->addressToSendEmailTo) > 0) {
            $shouldSend = true;

            if ($this->configuration->onlySendEmailIfNotSpam === true) {
                if ($comment->hasBeenCheckedForSpam() === false || $comment->isSpam() === true) {
                    $shouldSend = false;
                }
            } else {
                if ($this->guardConfiguration->autoDeleteSpam === true && $comment->hasBeenCheckedForSpam() === false) {
                    // Wait for spam guard so we don't send an email that may be auto-deleted.
                    $shouldSend = false;
                }
            }

            if ($shouldSend) {
                $this->mailer->sendMailForComment($comment);
            }
        }
    }
}
