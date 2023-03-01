<?php

namespace Stillat\Meerkat\Core\Guard;

use Serializable;
use Stillat\Meerkat\Core\Support\Arr;
use Stillat\Meerkat\Core\UuidGenerator;

/**
 * Class SpamReason
 *
 * Represents a reason why an item was identified as spam, from a spam Guard.
 *
 * @since 2.0.0
 */
class SpamReason implements Serializable
{
    const KEY_ID = 'id';

    const KEY_CODE = 'code';

    const KEY_TEXT = 'text';

    const KEY_CONTEXT = 'context';

    const KEY_NAME = 'name';

    const KEY_CLASS = 'class';

    /**
     * The Guard's internal reason code.
     *
     * @var string
     */
    protected $reasonCode = '';

    /**
     * A generalized reason explanation.
     *
     * @var string
     */
    protected $reasonText = '';

    /**
     * The reason's context, specific to each Guard implementation.
     *
     * @var mixed
     */
    protected $reasonContext = '';

    /**
     * The reason's identifier.
     *
     * @var string
     */
    protected $reasonId = '';

    /**
     * The guard's friendly name.
     *
     * @var string
     */
    protected $spamGuardName = '';

    /**
     * The guard class name.
     *
     * @var string
     */
    protected $spamGuardClass = '';

    public function __construct()
    {
        $this->reasonId = UuidGenerator::getInstance()->newId();
    }

    /**
     * Converts the array to a SpamReason.
     *
     * @param  array  $array The reason data.
     * @return SpamReason
     */
    public static function fromArray($array)
    {
        $reason = new SpamReason();

        if (Arr::matches([
            self::KEY_ID, self::KEY_CODE, self::KEY_CONTEXT, self::KEY_TEXT,
            self::KEY_CLASS, self::KEY_NAME,
        ], $array)) {
            $reason->setReasonId($array[self::KEY_ID]);
            $reason->setReasonCode($array[self::KEY_CODE]);
            $reason->setReasonContext($array[self::KEY_CONTEXT]);
            $reason->setReasonText($array[self::KEY_TEXT]);
            $reason->setGuardClass($array[self::KEY_CLASS]);
            $reason->setGuardName($array[self::KEY_NAME]);
        }

        return $reason;
    }

    /**
     * Sets the guard's fully-qualified class name.
     *
     * @param  string  $guardClass The guard's fully-qualified class name.
     */
    public function setGuardClass($guardClass)
    {
        $this->spamGuardClass = $guardClass;
    }

    /**
     * Sets the Guard's name.
     *
     * @param  string  $guardName The guard name.
     */
    public function setGuardName($guardName)
    {
        $this->spamGuardName = $guardName;
    }

    /**
     * Gets the reason's context.
     *
     * @return mixed|string
     */
    public function getReasonContext()
    {
        return $this->reasonContext;
    }

    /**
     * Sets the reason's context.
     *
     * @param  mixed|string  $context The reason's context.
     */
    public function setReasonContext($context)
    {
        $this->reasonContext = $context;
    }

    /**
     * Gets the reason's internal code.
     *
     * @return string
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * Sets the code's internal reason.
     *
     * @param  string  $code The reason code.
     */
    public function setReasonCode($code)
    {
        $this->reasonCode = $code;
    }

    /**
     * Gets the reason's generalized reason explanation.
     *
     * @return string
     */
    public function getReasonText()
    {
        return $this->reasonText;
    }

    /**
     * Sets the reason's generalized explanation.
     *
     * @param  string  $text The reason text.
     */
    public function setReasonText($text)
    {
        $this->reasonText = $text;
    }

    /**
     * Gets the reason's identifier.
     *
     * @return string
     */
    public function getReasonId()
    {
        return $this->reasonId;
    }

    /**
     * Sets the reason's identifier.
     *
     * @param  string  $reasonId The identifier.
     */
    public function setReasonId($reasonId)
    {
        $this->reasonId = $reasonId;
    }

    public function serialize()
    {
        return json_encode($this->toArray());
    }

    /**
     * Converts the SpamReason into an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::KEY_ID => $this->reasonId,
            self::KEY_CODE => $this->reasonCode,
            self::KEY_CONTEXT => $this->reasonContext,
            self::KEY_NAME => $this->spamGuardName,
            self::KEY_CLASS => $this->spamGuardClass,
            self::KEY_TEXT => $this->reasonText,
        ];
    }

    /**
     * Gets the spam guard's name.
     *
     * @return string
     */
    public function getGuardName()
    {
        return $this->spamGuardName;
    }

    /**
     * Gets the guard's fully-qualified class name.
     *
     * @return string
     */
    public function getGuardClass()
    {
        return $this->spamGuardClass;
    }

    public function unserialize($serialized)
    {
        $arrayFormat = (array) json_decode($serialized);

        $this->reasonId = $arrayFormat[self::KEY_ID];
        $this->reasonCode = $arrayFormat[self::KEY_CODE];
        $this->reasonContext = $arrayFormat[self::KEY_CONTEXT];
        $this->reasonText = $arrayFormat[self::KEY_TEXT];
        $this->spamGuardName = $arrayFormat[self::KEY_NAME];
        $this->spamGuardClass = $arrayFormat[self::KEY_CLASS];
    }
}
