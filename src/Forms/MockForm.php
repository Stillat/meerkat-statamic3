<?php

namespace Stillat\Meerkat\Forms;

use Statamic\Forms\Form;
use Stillat\Meerkat\Addon;

/**
 * Class MockForm
 *
 * Mocks the Statamic form object to assist with addon event compatibility.
 *
 * @package Stillat\Meerkat\Forms
 * @since 2.1.13
 */
class MockForm extends Form
{

    public function __construct()
    {
        $this->handle = Addon::ADDON_NAME;
    }

}
