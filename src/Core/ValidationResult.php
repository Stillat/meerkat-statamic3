<?php

namespace Stillat\Meerkat\Core;

/**
 * Class ValidationResult
 *
 * A consistent way to represent validity between systems
 *
 * @since 2.0.0
 */
class ValidationResult
{
    use DataObject;

    /**
     * Indicates if validation was a success.
     *
     * @var bool
     */
    public $isValid = false;

    /**
     * A list of reasons why validation failed.
     *
     * @var array
     */
    public $reasons = [];

    /**
     * A collection of additional validation attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Adds a new failure reason to the validation result.
     *
     * @param  string  $errorCode The error code for the result.
     * @param  string  $msg A human-readable version of the message.
     */
    public function add($errorCode, $msg)
    {
        $this->reasons[] = [
            'code' => $errorCode,
            'msg' => $msg,
        ];
    }

    /**
     * Merges the reasons with the current results and updates the validity.
     *
     * @param  string[]  $reasons The validation results to merge.
     * @return void
     */
    public function mergeReasons($reasons)
    {
        $this->reasons = array_merge($this->reasons, $reasons);
        $this->updateValidity();
    }

    /**
     * Updates the `$isValid` member based on the number of reasons provided.
     *
     * @return void
     */
    public function updateValidity()
    {
        $this->isValid = (count($this->reasons) == 0);
    }

    /**
     * Indicates if the current results contains the provided error code.
     *
     * @param  string  $errorCode The error code to test.
     * @return bool
     */
    public function containsError($errorCode)
    {
        if ($this->reasons == null || is_array($this->reasons) == false || count($this->reasons) == 0) {
            return false;
        }

        foreach ($this->reasons as $reason) {
            if ($reason == null || is_array($reason) == false || count($reason) == 0) {
                return false;
            }

            if (array_key_exists('code', $reason) == false) {
                return false;
            }

            if ($reason['code'] == $errorCode) {
                return true;
            }
        }

        return false;
    }
}
