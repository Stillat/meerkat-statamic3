<?php

use Statamic\Statamic;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Core\Guard\Providers\AkismetSpamGuard;
use Stillat\Meerkat\Core\Guard\Providers\DeceptiveMarkupGuard;
use Stillat\Meerkat\Core\Guard\Providers\IpFilterSpamGuard;
use Stillat\Meerkat\Core\Guard\Providers\WordFilterSpamGuard;
use Stillat\Meerkat\Core\Guard\SpamService;
use Stillat\Meerkat\Providers\AddonServiceProvider;
use Stillat\Meerkat\Support\Facades\Meerkat;

Meerkat::onRegisteringControlPanel(function () {
    Statamic::script('meerkat', Addon::VERSION.AddonServiceProvider::getResourceJavaScriptPath('/meerkatAvatars'));
});

// Register our guards with the discovery system so they are available for configuration.
Meerkat::onGuardStarting(function (SpamService $spamService) {
    $spamService->makeAvailable(trans('meerkat::guards.akismet'), AkismetSpamGuard::class);
    $spamService->makeAvailable(trans('meerkat::guards.word'), WordFilterSpamGuard::class);
    $spamService->makeAvailable(trans('meerkat::guards.ip'), IpFilterSpamGuard::class);
    $spamService->makeAvailable(trans('meerkat::guards.deceptive'), DeceptiveMarkupGuard::class);
});
