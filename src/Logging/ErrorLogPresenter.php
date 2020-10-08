<?php

namespace Stillat\Meerkat\Logging;

use Statamic\Extend\AddonRepository;
use Statamic\Facades\YAML;
use Statamic\Statamic;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Logging\ErrorLog;
use Stillat\Meerkat\Core\Logging\ErrorLogContext;
use Stillat\Meerkat\Core\Support\Str;

/**
 * Class ErrorLogPresenter
 *
 * Provides utilities for aggregating installation details to prepare an error log.
 *
 * @package Stillat\Meerkat\Logging
 * @since 2.0.0
 */
class ErrorLogPresenter
{
    use UsesConfig;

    /**
     * The AddonRepository instance.
     *
     * @var AddonRepository
     */
    protected $addons = null;

    /**
     * The installation's base directory.
     *
     * @var string
     */
    protected $baseDir = '';

    public function __construct(AddonRepository $addons)
    {
        $this->addons = $addons;
        $this->baseDir = base_path();
    }

    /**
     * Converts the provided error log into a string with relevant version details.
     *
     * @param ErrorLog $errorLog The error log to process.
     * @return string
     */
    public function present(ErrorLog $errorLog)
    {
        $report = [
            'generated' => gmdate("Y-m-d\TH:i:s\Z"),
            'versions' => [
                'php' => phpversion(),
                'statamic' => Statamic::version(),
                'meerkat' => Addon::VERSION
            ]
        ];

        if ($this->getConfig('telemetry.errors.submit_addon_data', true) === true) {
            $reportAddons = [];
            /**
             * @var string $key
             * @var \Statamic\Extend\Addon $addon
             */
            foreach ($this->addons->all() as $key => $addon) {
                $addonData = [
                    'name' => $addon->name(),
                    'vendor' => $addon->vendorName(),
                ];

                $rawAttributes = (array)($addon);
                foreach ($rawAttributes as $k => $attribute) {
                    if (Str::endsWith($k, 'version') && Str::endsWith($k, 'latestVersion') === false) {
                        $addonData['version'] = $attribute;
                        break;
                    }
                }
                unset($rawAttributes);
                $reportAddons[] = $addonData;
            }

            $report['addons'] = $reportAddons;
        }

        $report['error_code'] = $errorLog->errorCode;
        $report['actionId'] = $errorLog->action;
        $report['log_date'] = $errorLog->dateTimeUtc;
        $report['error_type'] = $errorLog->type;

        $reportContent = '';

        if ($errorLog->context !== null && $errorLog->context instanceof ErrorLogContext) {
            $report['error_msg'] = $this->prepare($errorLog->context->msg);
            $reportContent = $errorLog->context->details;
        } else {
            $report['error_msg'] = $this->prepare($errorLog->context);
        }

        $reportContent = $this->prepare($reportContent);

        return YAML::dump($report, $reportContent);
    }

    /**
     * Prepares the content for submission.
     *
     * @param string $content The content to prepare.
     * @return string|string[]
     */
    private function prepare($content)
    {
        $content = str_replace($this->baseDir, '##BASEDIR##', $content);

        return $content;
    }

}
