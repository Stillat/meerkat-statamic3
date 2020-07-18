<?php

namespace Stillat\Meerkat\Core\Guard\Providers;

use Stillat\Meerkat\Core\Support\Str;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\GuardConfiguration;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardContract;

/**
 * Class WordFilterSpamGuard
 *
 * Determines if a comment is spam by checking against a list of unfavorable words
 *
 * @package Stillat\Meerkat\Core\Guard\Providers
 * @since 2.0.0
 */
class WordFilterSpamGuard implements SpamGuardContract
{

    /**
     * The Meerkat Guard configuration instance.
     *
     * @var GuardConfiguration
     */
    private $guardConfig = null;

    public function __construct(GuardConfiguration $config)
    {
        $this->guardConfig = $config;
    }

    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public function getName()
    {
        return 'WordFilter';
    }

    /**
     * Gets a value indicating if the detector succeeded.
     *
     * @return boolean
     */
    public function wasSuccess()
    {
        return true;
    }

    /**
     * Returns a value indicating if the provided comment has a
     * high probability of being a disingenuous posting.
     *
     * @param DataObjectContract $data
     *
     * @return boolean
     */
    public function getIsSpam(DataObjectContract $data)
    {
        if (count($this->guardConfig->bannedWords) === 0) {
            return false;
        }

        $emailAddress = $data->getDataAttribute(AuthorContract::KEY_EMAIL_ADDRESS);
        $name = $data->getDataAttribute(AuthorContract::KEY_NAME);
        $content = $data->getDataAttribute(CommentContract::KEY_COMMENT);
        $contentComment = $data->getDataAttribute(CommentContract::KEY_CONTENT);
    
        foreach ($this->guardConfig->bannedWords as $word) {
            if (Str::contains($emailAddress, $word)) {
                return true;
            }

            if (Str::contains($name, $word)) {
                return true;
            }

            if (Str::contains($content, $word)) {
                return true;
            }

            if (Str::contains($contentComment, $word)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Marks a comment as a spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     * @param  DataObjectContract $data
     *
     * @return boolean
     */
    public function markAsSpam(DataObjectContract $data)
    {
        return false;
    }

    /**
     * Marks a comment as not-spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     * @param  DataObjectContract $data
     *
     * @return boolean
     */
    public function markAsHam(DataObjectContract $data)
    {
        return false;
    }

    /**
     * Returns a value indicating if a guard supports submitting
     * not-spam results to a third-party service or product.
     *
     * @return boolean
     */
    public function supportsSubmittingHam()
    {
        return false;
    }

    /**
     * Returns a value indicating if a guard supports submitting
     * spam results to a third-party service or product.
     *
     * @return boolean
     */
    public function supportsSubmittingSpam()
    {
        return false;
    }

    /**
     * Returns a value indicating if the guard encountered errors.
     *
     * @since 2.0.0
     * @return boolean
     */
    public function hasErrors()
    {
        return false;
    }

    /**
     * Returns a collection of errors, if available.
     *
     * @return array
     */
    public function getErrors()
    {
        return [];
    }

}
