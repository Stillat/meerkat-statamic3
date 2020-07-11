<?php

namespace Stillat\Meerkat\Identity;

use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\DataObject;

class StatamicIdentity implements AuthorContract
{
    use DataObject;

    protected $attributes = [];


    public function getIsTransient()
    {
        // TODO: Implement getIsTransient() method.
    }

    public function setIsTransient($isTransient)
    {
        // TODO: Implement setIsTransient() method.
    }

    public function getId()
    {
        // TODO: Implement getId() method.
    }

    public function setId($userId)
    {
        // TODO: Implement setId() method.
    }

    public function getDisplayName()
    {
        // TODO: Implement getDisplayName() method.
    }

    public function setDisplayName($displayName)
    {
        // TODO: Implement setDisplayName() method.
    }

}