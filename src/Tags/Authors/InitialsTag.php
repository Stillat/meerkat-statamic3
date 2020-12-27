<?php

namespace Stillat\Meerkat\Tags\Authors;

use Stillat\Meerkat\Core\Authoring\InitialsGenerator;
use Stillat\Meerkat\Core\Support\TypeConversions;
use Stillat\Meerkat\Tags\MeerkatTag;

/**
 * Class InitialsTag
 *
 * Provides access to Meerkat's initial generation system from within Antlers templates.
 *
 * @package Stillat\Meerkat\Tags\Authors
 * @since 2.1.18
 */
class InitialsTag extends MeerkatTag
{

    /**
     * Renders the tag content.
     *
     * @return string
     */
    public function render()
    {
        $value = $this->getParameterValue('value', null);
        $separator = $this->getParameterValue('separator', ' ');
        $onlyCaps = TypeConversions::getBooleanValue($this->getParameterValue('only_caps', false));

        return InitialsGenerator::getInitials($value, $onlyCaps, $separator);
    }

}
