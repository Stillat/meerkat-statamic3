<?php

namespace Stillat\Meerkat\Core\Comments;

/**
 * Class VariableSuccessResult
 *
 * Represents the return results of actions against multiple comments.
 *
 * @package Stillat\Meerkat\Core\Comments
 * @since 2.0.0
 */
class VariableSuccessResult
{

    /**
     * Indicates if all actions succeeded.
     *
     * @var bool
     */
    public $success = false;

    /**
     * A collection of all affected comment identifiers.
     *
     * @var array
     */
    public $comments = [];

    /**
     * A collection of all succeeded actions.
     *
     * @var array
     */
    public $succeeded = [];

    /**
     * A collection of all failed actions.
     *
     * @var array
     */
    public $failed = [];

}
