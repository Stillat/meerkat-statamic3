<?php

namespace Stillat\Meerkat\Core\Handlers;

use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Mail\Mailer;

/**
 * Class EmailHandler
 *
 * Handles the interaction between the mailer implementations and the underlying storage systems.
 *
 * @package Stillat\Meerkat\Core\Handlers
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
     * The Mailer instance.
     *
     * @var Mailer
     */
    protected $mailer = null;

    public function __construct(Configuration $configuration, Mailer $mailer)
    {
        $this->configuration = $configuration;
        $this->mailer = $mailer;
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
            }

            if ($shouldSend) {
                $this->mailer->sendMailForComment($comment);
            }
        }
    }

}
