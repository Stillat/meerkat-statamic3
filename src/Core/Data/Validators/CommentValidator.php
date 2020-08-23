<?php

namespace Stillat\Meerkat\Core\Data\Validators;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/**
 * Class CommentValidator
 *
 * Provides utilities for proving a value is a comment.
 *
 * @package Stillat\Meerkat\Core\Data\Validators
 * @since 2.0.0
 */
class CommentValidator
{

    /**
     * Tests if the value is a valid instance of CommentContract.
     *
     * @param mixed $inputValue The value to check.
     * @return bool
     */
    public static function check($inputValue)
    {
        if ($inputValue !== null && $inputValue instanceof CommentContract) {
            return true;
        }

        return false;
    }

}
