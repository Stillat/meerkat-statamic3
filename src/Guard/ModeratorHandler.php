<?php

namespace Stillat\Meerkat\Guard;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Guard\SubmitModeratorResultsHandler;

/**
 * Class ModeratorHandler
 *
 * Coordinates the interactions between the Statamic addon and Meerkat Core for the submission of moderator results.
 *
 * @since 2.08
 */
class ModeratorHandler
{
    /**
     * The moderator submission handler.
     *
     * @var SubmitModeratorResultsHandler
     */
    protected $handler = null;

    public function __construct(SubmitModeratorResultsHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Asks Meerkat Core to submit the results to third-party spam guard providers, if they support it.
     *
     * @param  CommentContract  $comment The comment to check.
     * @param  bool  $isSpam Indicates if the comment is spam.
     */
    public function submitToProviders(CommentContract $comment, $isSpam)
    {
        $this->handler->submitToProviders($comment, $isSpam);
    }
}
