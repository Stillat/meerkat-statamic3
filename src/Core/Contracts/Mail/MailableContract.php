<?php

namespace Stillat\Meerkat\Core\Contracts\Mail;

interface MailableContract
{

    public function getSubject();

    public function getBody();

    public function isMultiView();

}
