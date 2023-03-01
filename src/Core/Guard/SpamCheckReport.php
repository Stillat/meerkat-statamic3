<?php

namespace Stillat\Meerkat\Core\Guard;

use Serializable;
use Stillat\Meerkat\Core\Support\Arr;
use Stillat\Meerkat\Core\UuidGenerator;

/**
 * Class SpamCheckReport
 *
 * Represents the results of a spam check operation.
 *
 * @since 2.0.0
 */
class SpamCheckReport implements Serializable
{
    const KEY_ID = 'id';

    const KEY_DATETIME = 'date';

    const KEY_REASONS = 'reasons';

    const KEY_SKIPPED_GUARDS = 'skipped_guards';

    const KEY_DETECTED_SPAM = 'detected_spam';

    /**
     * The check report instance identifier.
     *
     * @var string
     */
    protected $reportId = '';

    /**
     * The match reasons.
     *
     * @var SpamReason[]
     */
    protected $reasons = [];

    /**
     * The report's creation date/time UTC.
     *
     * @var int
     */
    protected $dateTime = 0;

    /**
     * Indicates if any spam guards were skipped due to configuration.
     *
     * @var bool
     */
    protected $skippedGuards = false;

    /**
     * Indicates if any spam guard detected spam.
     *
     * @var bool
     */
    protected $detectedSpam = false;

    public function __construct()
    {
        $this->reportId = UuidGenerator::getInstance()->newId();
        $this->dateTime = time();
    }

    /**
     * Converts the array to a SpamCheckReport.
     *
     * @param  array  $array The report data.
     * @return SpamCheckReport|null
     */
    public static function fromArray($array)
    {
        if (Arr::matches([
            self::KEY_ID, self::KEY_DATETIME, self::KEY_DETECTED_SPAM, self::KEY_SKIPPED_GUARDS, self::KEY_REASONS,
        ], $array) === false) {
            return null;
        }
        $report = new SpamCheckReport();

        $report->setId($array[self::KEY_ID]);
        $report->setDateTime($array[self::KEY_DATETIME]);
        $report->setDetectedSpam($array[self::KEY_DETECTED_SPAM]);
        $report->setSkippedGuards($array[self::KEY_SKIPPED_GUARDS]);

        $reasons = $array[self::KEY_REASONS];

        foreach ($reasons as $reason) {
            $report->addReason(SpamReason::fromArray($reason));
        }

        return $report;
    }

    /**
     * Gets the report's identifier.
     *
     * @param  string  $id The identifier.
     */
    public function setId($id)
    {
        $this->reportId = $id;
    }

    /**
     * @param  SpamReason  $reason Adds a spam reason.
     */
    public function addReason($reason)
    {
        $this->reasons[] = $reason;
    }

    /**
     * Returns the report's UTC timestamp.
     *
     * @return int
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Sets the report's UTC timestamp.
     *
     * @param  int  $dateTime The timestamp.
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * Gets the report's identifier.
     *
     * @return string
     */
    public function getId()
    {
        return $this->reportId;
    }

    /**
     * Sets the report's spam reasons.
     *
     * @param  SpamReason[]  $reasons The reasons.
     */
    public function setGuardReasons($reasons)
    {
        $this->reasons = $reasons;
    }

    /**
     * Gets the report's spam reasons.
     *
     * @return SpamReason[]
     */
    public function getGuardReasons()
    {
        return $this->reasons;
    }

    /**
     * Returns if any guards were skipped during the operation, due to configuration.
     *
     * @return bool
     */
    public function getSkippedGuards()
    {
        return $this->skippedGuards;
    }

    /**
     * Sets whether spam guards were skipped due to configuration.
     *
     * @param  bool  $skipped Whether guards were skipped.
     */
    public function setSkippedGuards($skipped)
    {
        $this->skippedGuards = $skipped;
    }

    /**
     * Returns if any spam guards detected spam.
     *
     * @return bool
     */
    public function getDetectedSpam()
    {
        return $this->detectedSpam;
    }

    /**
     * Sets whether any spam guards detected spam.
     *
     * @param  bool  $foundSpam Whether spam was detected.
     */
    public function setDetectedSpam($foundSpam)
    {
        $this->detectedSpam = $foundSpam;
    }

    public function serialize()
    {
        return json_encode($this->toArray());
    }

    /**
     * Converts the spam report to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $arrayReasons = [];

        foreach ($this->reasons as $reason) {
            $arrayReasons[] = $reason->toArray();
        }

        return [
            self::KEY_ID => $this->reportId,
            self::KEY_REASONS => $arrayReasons,
            self::KEY_DATETIME => $this->dateTime,
            self::KEY_SKIPPED_GUARDS => $this->skippedGuards,
            self::KEY_DETECTED_SPAM => $this->detectedSpam,
        ];
    }

    public function unserialize($serialized)
    {
        $arrayFormat = (array) json_decode($serialized);

        $this->reportId = $arrayFormat[self::KEY_ID];
        $this->reasons = $arrayFormat[self::KEY_REASONS];
        $this->dateTime = $arrayFormat[self::KEY_DATETIME];
        $this->skippedGuards = $arrayFormat[self::KEY_SKIPPED_GUARDS];
        $this->detectedSpam = $arrayFormat[self::KEY_DETECTED_SPAM];
    }
}
