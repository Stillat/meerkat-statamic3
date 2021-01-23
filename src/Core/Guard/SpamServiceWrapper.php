<?php

namespace Stillat\Meerkat\Core\Guard;

use Stillat\Meerkat\Core\Comments\Comment;

/**
 * Class SpamServiceWrapper
 *
 * Provides a wrapper around Meerkat's spam service implementation.
 *
 * @package Stillat\Meerkat\Core\Guard
 * @since 2.2.0
 */
class SpamServiceWrapper
{

    /**
     * The SpamService instance.
     *
     * @var SpamService|null
     */
    protected $spamService = null;

    public function __construct(SpamService $spamService)
    {
        $this->spamService = $spamService;
    }

    /**
     * Checks if the provided data is spam or not.
     *
     * @param Specimen $specimen The data to check.
     * @return boolean
     */
    public function isSpam(Specimen $specimen)
    {
        return $this->spamService->isSpam($this->convertSpecimenToComment($specimen), false);
    }

    /**
     * Submits the specimen as spam to any third-party spam service providers.
     *
     * @param Specimen $specimen The data to submit.
     * @return GuardResult
     */
    public function submitSpam(Specimen $specimen)
    {
        return $this->spamService->submitAsSpam($specimen);
    }

    /**
     * Submits the specimen as not spam to any third-party spam service providers.
     *
     * @param Specimen $specimen The data to submit.
     * @return GuardResult
     */
    public function submitHam(Specimen $specimen)
    {
        return $this->spamService->submitAsHam($specimen);
    }

    /**
     * Converts the provided specimen to a Comment instance for SpamService compatibility.
     *
     * @param Specimen $specimen The specimen to convert.
     * @return Comment
     */
    private function convertSpecimenToComment(Specimen $specimen)
    {
        $comment = new Comment();
        $comment->setDataAttributes($specimen->getDataAttributes());

        return $comment;
    }

}
