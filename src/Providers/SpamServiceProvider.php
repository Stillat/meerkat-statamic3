<?php

namespace Stillat\Meerkat\Providers;

use Exception;
use Illuminate\Support\Facades\Event;
use Statamic\Statamic;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardPipelineContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\Guard\SpamChecker;
use Stillat\Meerkat\Core\Guard\SpamCheckerFactory;
use Stillat\Meerkat\Core\Guard\SpamService;
use Stillat\Meerkat\Core\GuardConfiguration;
use Stillat\Meerkat\Core\Logging\ErrorLog;
use Stillat\Meerkat\Core\Logging\ErrorLogContext;
use Stillat\Meerkat\Core\Logging\LocalErrorCodeRepository;
use Stillat\Meerkat\Guard\FormHandler;
use Stillat\Meerkat\Guard\GuardPipeline;

class SpamServiceProvider extends AddonServiceProvider
{
    use UsesConfig;

    public function register()
    {
        $this->app->singleton(SpamGuardPipelineContract::class, function ($app) {
            return $app->make(GuardPipeline::class);
        });

        $this->app->singleton(SpamService::class, function ($app) {
            $guardConfig = app(GuardConfiguration::class);
            $pipeline = app(SpamGuardPipelineContract::class);

            return new SpamService($guardConfig, $pipeline);
        });

        // TODO: Deprecated. Use Core Handlers.
        Event::listen(['Meerkat.comments.created', 'Meerkat.comments.updated'], function (CommentContract $comment) {
            /** @var FormHandler $handler */
            $handler = app(FormHandler::class);

            $handler->checkForSpam($comment);
        });


        Event::listen('Meerkat.comments.created', function () {
            /** @var FormHandler $guardHandler */
            $guardHandler = app(FormHandler::class);
        });

        Statamic::booted(function () {
            /** @var SpamService $spamService */
            $spamService = app(SpamService::class);

            SpamCheckerFactory::$factoryMethod = function () {
                return app()->make(SpamChecker::class);
            };

            $guardConfiguration = $this->getConfig('publishing.guards', []);

            if (is_array($guardConfiguration) === false) {
                $guardConfiguration = [];
            }

            foreach ($guardConfiguration as $guard) {
                if (class_exists($guard)) {
                    try {
                        $instance = app()->make($guard);

                        if ($instance instanceof SpamGuardContract) {
                            $spamService->registerGuard($instance);
                        } else {
                            $message = $guard . ' cannot be registered; it is not an instance of '
                                . SpamGuardContract::class;

                            LocalErrorCodeRepository::log(ErrorLog::warning(
                                Errors::GUARD_INCORRECT_TYPE,
                                $message
                            ));
                        }
                    } catch (Exception $e) {
                        $errorContext = new ErrorLogContext();
                        $errorContext->msg = $e->getMessage();
                        $errorContext->details = $e->getTraceAsString();

                        LocalErrorCodeRepository::log(ErrorLog::make(Errors::GUARD_CREATION_FAILED, $errorContext));
                    }
                } else {
                    LocalErrorCodeRepository::log(ErrorLog::warning(
                        Errors::GUARD_MISSING_TYPE,
                        $guard . ' class could not be located.'
                    ));
                }
            }
        });
    }

}
