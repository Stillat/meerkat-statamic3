<?php

namespace Stillat\Meerkat\Core;

/**
 * Class ValidationResult
 *
 * A consistent way to represent validity between systems
 *
 * @package Stillat\Meerkat\Core
 * @since 2.0.0
 */
class ValidationResult
{

    /**
     * Indicates if validation was a success.
     *
     * @var boolean
     */
    public $isValid = false;

    /**
     * A list of reasons why validation failed.
     *
     * @var array
     */
    public $reasons = [];

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
     * Merges the reasons with the current results and updates the validity.
     *
     * @param  string[] $reasons The validation results to merge.
     *
     * @return void
     */
    public function mergeReasons($reasons)
    {
        $this->reasons = array_merge($this->reasons, $reasons);
        $this->updateValidity();
    }
}
