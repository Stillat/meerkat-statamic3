<?php

namespace Stillat\Meerkat\Core\Data\Helpers;

use Stillat\Meerkat\Core\Data\Filters\CommentFilter;
use Stillat\Meerkat\Core\Identity\IdentityManagerFactory;

/**
 * Class UserHelpers
 *
 * Provides utilities for retrieving contextual user lists.
 *
 * @package Stillat\Meerkat\Core\Data\Helpers
 * @since 2.0.0
 */
class UserHelpers
{

    /**
     * Generates a list of user identifiers.
     *
     * @param array $knownIds The known user identifiers.
     * @return array
     */
    public static function buildContextualUserList($knownIds)
    {
        $userList = [];

        foreach ($knownIds as $id) {
            if ($id === CommentFilter::PARAM_GLOBAL_CURRENT) {
                if (IdentityManagerFactory::hasInstance()) {
                    $currentIdentity = IdentityManagerFactory::$instance->getIdentityContext();

                    if ($currentIdentity !== null) {
                        $userList[] = $currentIdentity->getId();
                    }
                }
            } else {
                $userList[] = $id;
            }
        }

        return $userList;
    }

}
