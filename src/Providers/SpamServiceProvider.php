<?php

namespace Stillat\Meerkat\Providers;

use Exception;
use Statamic\Statamic;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Contracts\SpamGuardContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Guard\SpamService;
use Stillat\Meerkat\Core\GuardConfiguration;
use Stillat\Meerkat\Core\Logging\ErrorLog;
use Stillat\Meerkat\Core\Logging\ErrorLogContext;
use Stillat\Meerkat\Core\Logging\LocalErrorCodeRepository;

class SpamServiceProvider extends AddonServiceProvider
{
    use UsesConfig;

    public function register()
    {
        $this->app->singleton(SpamService::class, function ($app) {
            $guardConfig = app(GuardConfiguration::class);

            return new SpamService($guardConfig);
        });

        Statamic::booted(function () {
            /** @var SpamService $spamService */
            $spamService = app(SpamService::class);

            foreach ($this->getConfig('publishing.guards') as $guard) {
                if (class_exists($guard)) {
                    try {
                        $instance = app()->make($guard);

                        if ($instance instanceof SpamGuardContract) {
                            $spamService->registerGuard($instance);
                        }
                    } catch (Exception $e) {
                        $errorContext = new ErrorLogContext();
                        $errorContext->msg = $e->getMessage();
                        $errorContext->details = $e->getTraceAsString();

                        LocalErrorCodeRepository::log(ErrorLog::make(Errors::GUARD_CREATION_FAILED, $errorContext));
                    }
                }
            }
        });
    }

}
