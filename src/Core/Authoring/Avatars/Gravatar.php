<?php

namespace Stillat\Meerkat\Core\Authoring\Avatars;

/**
 * Class Gravatar
 *
 * Provides utilities for generating Gravatar URLs
 *
 * Gravatars is an image service that allows users
 * to utilize the same image across many different
 * sites: https://en.gravatar.com/site/implement/
 *
 * @since 2.0.0
 */
class Gravatar
{
    /**
     * Returns the value expected by Gravatar for the provided email address.
     *
     * @param  string  $emailAddress
     * @return string
     */
    public static function gravatarValue($emailAddress)
    {
        return md5($emailAddress);
    }

    /**
     * Returns a Gravatar URI for the provided email address.
     *
     * @param  string  $email
     * @return string
     */
    public function gravatar($email)
    {
        return '//www.gravatar.com/avatar/'.md5($email).'?';
    }
}
