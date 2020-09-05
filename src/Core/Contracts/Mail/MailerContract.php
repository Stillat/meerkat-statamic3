<?php

namespace Stillat\Meerkat\Core\Contracts\Mail;

interface MailerContract
{

    public function send(MailableContract $message);

}
