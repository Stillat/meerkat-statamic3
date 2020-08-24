<?php

namespace Stillat\Meerkat\Guard;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Guard\SpamService;

class FormHandler
{

    /**
     * The SpamService instance.
     *
     * @var SpamService
     */
    protected $spamService = null;

    public function __construct(SpamService $service)
    {
        $this->spamService = $service;
    }

    public function checkForSpam(CommentContract $comment)
    {
        $isSpam = $this->spamService->isSpam($comment);
        dd('inside form handler', $comment, $isSpam, $this->spamService);
    }

}
