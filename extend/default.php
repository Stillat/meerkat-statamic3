<?php

use Statamic\Statamic;
use Illuminate\Support\Facades\Event;

Event::listen('Meerkat.registering.controlPanel', function () {
    Statamic::script('meerkat', \Stillat\Meerkat\Addon::VERSION.'/meerkatAvatars');
});
