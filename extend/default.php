<?php

use Statamic\Statamic;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Support\Facades\Meerkat;

Meerkat::onRegisteringControlPanel(function () {
    Statamic::script('meerkat', Addon::VERSION . '/meerkatAvatars');
});
