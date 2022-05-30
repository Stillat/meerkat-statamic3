<?php

namespace Stillat\Meerkat\Core\Contracts\Mail;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

interface MailerContract
{

    /**
     * Sends a comment submission notification to the list of email addresses.
     *
     * @param string[] $addresses The email addresses to send to.
     * @param CommentContract $comment The comment to sent.
     * @return bool
     */
    public function send($addresses, CommentContract $comment);

    /**
     * Sets an optional "from" email address override.
     *
     * @param string|null $address The address.
     * @return $this
     */
    public function setFromAddress($address);

}
