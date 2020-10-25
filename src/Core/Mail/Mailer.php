<?php

namespace Stillat\Meerkat\Core\Mail;

use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Mail\MailerContract;

/**
 * Class Mailer
 *
 * A wrapper around the MailerContract system.
 *
 * @package Stillat\Meerkat\Core\Mail
 * @since 2.1.5
 */
class Mailer
{

    /**
     * The Meerkat Core Configuration container.
     *
     * @var Configuration
     */
    protected $config = null;

    /**
     * The MailerContract implementation instance.
     * f
     * @var MailerContract
     */
    protected $mailer = null;


    public function __construct(MailerContract $mailer, Configuration $config)
    {
        $this->mailer = $mailer;
        $this->config = $config;
    }


    /**
     * Sends an email for the provided comment.
     *
     * @param CommentContract $comment The comment.
     */
    public function sendMailForComment(CommentContract $comment)
    {
        $this->mailer->send($this->config->addressToSendEmailTo, $comment);
    }

}
