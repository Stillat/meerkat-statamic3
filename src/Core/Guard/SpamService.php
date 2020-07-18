<?php

namespace Stillat\Meerkat\Core\Guard;

use Exception;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardContract;
use Stillat\Meerkat\Core\GuardConfiguration;

/**
 * Class SpamService
 *
 * Provides a wrapper around the configured spam guards
 *
 * @package Stillat\Meerkat\Core\Guard
 * @since 2.0.0
 */
class SpamService
{

    /**
     * The settings to control Guard behavior.
     *
     * @var GuardConfiguration
     */
    protected $config = null;

    /**
     * A collection of spam guards used to check comments.
     *
     * @var array
     */
    protected $spamGuards = [];

    /**
     * A collection of exceptions thrown when interacting with third-party
     * spam service providers, if any. This is reset each time `isSpam`
     * is called; whether internally or externally. Use the methods
     * `hasErrors` and `getErrors` to inspect errors after calls.
     *
     * @var array
     */
    protected $errors = [];

    public function __construct(GuardConfiguration $config)
    {
        $this->config = $config;
    }

    /**
     * Registers a spam guard with the service.
     *
     * @param  SpamGuardContract $guard
     *
     * @return void
     */
    public function registerGuard(SpamGuardContract $guard)
    {
        $this->spamGuards[] = $guard;
    }

    /**
     * Returns a collection of Exceptions thrown during
     * communication with third-party spam providers.
     *
     * @return Exception[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Returns a value indicating if any errors were reported during
     * the previous execution of the `isSpam($comment)` method.
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * Returns a value indicating if any spam guards were registered.
     *
     * @return boolean
     */
    public function hasGuards()
    {
        return count($this->spamGuards) > 0;
    }

    /**
     * Iterates each spam guard and submits the comment as spam.
     *
     * @param  DataObjectContract $data
     *
     * @return GuardResult
     */
    public function submitAsSpam(DataObjectContract $data)
    {
        if ($data == null) {
            return GuardResult::failure();
        }

        if (!$this->hasGuards()) {
            return GuardResult::failure();
        }

        return $this->markAsSpam($data);
    }

    /**
     * Uses each registered spam guard to submit the comment as spam.
     *
     * @param  DataObjectContract $data
     *
     * @return GuardResult
     */
    private function markAsSpam(DataObjectContract $data)
    {
        $result = new GuardResult();
        $result->success = true;

        foreach ($this->spamGuards as $guard) {
            if ($guard->supportsSubmittingSpam()) {
                try {
                    $guard->markAsSpam($data);

                    if ($guard->hasErrors()) {
                        $result->success = false;
                        $this->errors = array_merge($this->errors, $guard->getErrors());
                    }
                } catch (Exception $e) {
                    $this->errors[] = $e;
                    $result->success = false;
                }
            }
        }

        $result->errors = $this->errors;

        return $result;
    }

    /**
     * Iterates each spam guard and submits the comment as "ham" (not spam).
     *
     * @param  DataObjectContract $data
     *
     * @return GuardResult
     */
    public function submitAsHam(DataObjectContract $data)
    {
        if ($data == null) {
            return new GuardResult();
        }

        if (!$this->hasGuards()) {
            return new GuardResult();
        }

        return $this->markAsHam($data);
    }

    /**
     * Uses each registered spam guard to submit the comment as spam.
     *
     * @param  DataObjectContract $data
     *
     * @return GuardResult
     */
    private function markAsHam(DataObjectContract $data)
    {
        $result = new GuardResult();
        $result->success = true;

        foreach ($this->spamGuards as $guard) {
            if ($guard->supportsSubmittingHam()) {
                try {
                    $guard->markAsHam($data);

                    if ($guard->hasErrors()) {
                        $result->success = false;
                        $this->errors = array_merge($this->errors, $guard->getErrors());
                    }
                } catch (Exception $e) {
                    $this->errors[] = $e;
                    $result->success = false;
                }
            }
        }

        $result->errors = $this->errors;

        return $result;
    }

    /**
     * Checks the provided comment against any registered spam guards.
     *
     * @param  CommentContract $comment
     *
     * @return boolean
     */
    public function isSpam(CommentContract $comment)
    {
        if ($comment == null) {
            return false;
        }

        if (!$this->hasGuards()) {
            return false;
        }

        // Reset the errors list.
        $this->errors = [];

        // Indicates the number of spam guards that have marked the comment as spam.
        $spamCount = 0;

        foreach ($this->spamGuards as $guard) {
            try {
                if ($guard->getIsSpam($comment)) {
                    $spamCount += 1;

                    if ($this->config->autoSubmitSpamToThirdParties) {
                        $guard->markAsSpam($comment);

                        if ($guard->hasErrors()) {
                            $this->errors = array_merge($this->errors, $guard->getErrors());

                            if ($this->config->unpublishOnGuardFailures) {
                                $comment->unpublish();
                            }
                        }
                    }

                    // If the configuration specifies that we should not check against
                    // all spam guard services after a positive match has been
                    // identified, we will not continue to check other guards.
                    if ($this->config->checkAgainstAllGuardServices == false) {
                        break;
                    }
                }
            } catch (Exception $e) {
                $this->errors[] = $e;

                // If we could not connect to the remote service, check if we
                // should unpublish any comments automatically that we could
                // not check reliably with the third-party spam service.
                if ($this->config->unpublishOnGuardFailures) {
                    $comment->unpublish();
                }
            }
        }

        return ($spamCount > 0);
    }

}
