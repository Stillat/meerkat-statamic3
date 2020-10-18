<?php

namespace Stillat\Meerkat\Core\Guard;

use Exception;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardPipelineContract;
use Stillat\Meerkat\Core\GuardConfiguration;
use Stillat\Meerkat\Core\Logging\ExceptionLoggerFactory;

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
    const KEY_NAME = 'name';
    const KEY_CLASS = 'class';

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

    /**
     * A collection of all available spam guards.
     *
     * @var array
     */
    protected $discoveredGuards = [];

    /**
     * The SpamGuardPipelineContract implementation instance.
     *
     * @var SpamGuardPipelineContract
     */
    protected $guardPipeline = null;

    /**
     * The last SpamCheckReport, if available.
     *
     * @var SpamCheckReport|null
     */
    protected $lastReport = null;

    public function __construct(GuardConfiguration $config, SpamGuardPipelineContract $pipeline)
    {
        $this->config = $config;
        $this->guardPipeline = $pipeline;

        $this->emitStartingEvent();
    }

    /**
     * Emits the starting event event to allow addons to register custom guards, if desired.
     */
    private function emitStartingEvent()
    {
        $this->guardPipeline->guardStarting($this, null);
    }

    /**
     * Registers a spam guard with the service.
     *
     * @param SpamGuardContract $guard
     *
     * @return void
     */
    public function registerGuard(SpamGuardContract $guard)
    {
        $this->spamGuards[] = $guard;
    }

    /**
     * Lets the spam service that a spam guard is available.
     *
     * This method does not automatically add the guard to the utilized guard list.
     *
     * @param string $guardName The guard's friendly name.
     * @param string $guardClass The fully-qualified class for the guard.
     * @since 2.0.12
     */
    public function makeAvailable($guardName, $guardClass)
    {
        $this->discoveredGuards[] = [
            self::KEY_NAME => $guardName,
            self::KEY_CLASS => $guardClass
        ];
    }

    /**
     * Returns all spam guards that have registered themselves for configuration access.
     *
     * @return array
     */
    public function getDiscoveredGuards()
    {
        return $this->discoveredGuards;
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
     * Iterates each spam guard and submits the comment as spam.
     *
     * @param DataObjectContract $data
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
     * Returns a value indicating if any spam guards were registered.
     *
     * @return boolean
     */
    public function hasGuards()
    {
        return count($this->spamGuards) > 0;
    }

    /**
     * Uses each registered spam guard to submit the comment as spam.
     *
     * @param DataObjectContract $data
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
                    ExceptionLoggerFactory::log($e);
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
     * @param DataObjectContract $data
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
     * @param DataObjectContract $data
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
                    ExceptionLoggerFactory::log($e);
                    $this->errors[] = $e;
                    $result->success = false;
                }
            }
        }

        $result->errors = $this->errors;

        return $result;
    }

    /**
     * Gets the last spam check report, if available.
     *
     * @return SpamCheckReport|null
     */
    public function getLastReport()
    {
        return $this->lastReport;
    }

    /**
     * Checks the provided comment against any registered spam guards.
     *
     * @param CommentContract $comment
     *
     * @return boolean
     */
    public function isSpam(CommentContract $comment)
    {
        $this->lastReport = null;

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

        $this->lastReport = new SpamCheckReport();
        /** @var SpamGuardContract $guard */
        foreach ($this->spamGuards as $guard) {
            try {
                if ($guard->getIsSpam($comment)) {
                    $spamCount += 1;

                    $guardReasons = $guard->getSpamReasons();

                    if ($guardReasons !== null && is_array($guardReasons)) {
                        /** @var SpamReason $reason */
                        foreach ($guardReasons as $reason) {
                            $reason->setGuardName($guard->getName());
                            $reason->setGuardClass(get_class($guard));

                            $this->lastReport->addReason($reason);
                        }
                    }

                    if ($guard->hasErrors()) {
                        $this->errors = array_merge($this->errors, $guard->getErrors());

                        if ($this->config->unpublishOnGuardFailures) {
                            $comment->unpublish();
                        }
                    }
                    // If the configuration specifies that we should not check against
                    // all spam guard services after a positive match has been
                    // identified, we will not continue to check other guards.
                    if ($this->config->checkAgainstAllGuardServices == false) {
                        $this->lastReport->setSkippedGuards(true);
                        break;
                    }
                }
            } catch (Exception $e) {
                ExceptionLoggerFactory::log($e);
                $this->errors[] = $e;

                // If we could not connect to the remote service, check if we
                // should unpublish any comments automatically that we could
                // not check reliably with the third-party spam service.
                if ($this->config->unpublishOnGuardFailures) {
                    $comment->unpublish();
                }
            }
        }

        $foundSpam = $spamCount > 0;

        $this->lastReport->setDetectedSpam($foundSpam);

        return $foundSpam;
    }

}
