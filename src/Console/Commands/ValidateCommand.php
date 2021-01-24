<?php

namespace Stillat\Meerkat\Console\Commands;

use Illuminate\Console\Command;
use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Feedback\SolutionProvider;
use Stillat\Meerkat\PathProvider;
use Stillat\Meerkat\Validation\StorageDriverValidator;

/**
 * Class ValidateCommand
 *
 * Provides utilities for validating the Meerkat installation.
 *
 * @package Stillat\Meerkat\Console\Commands
 * @since 2.0.0
 */
class ValidateCommand extends Command
{
    use UsesTranslations;

    /**
     * The command's signature.
     *
     * php please meerkat:validate
     *
     * @var string
     */
    protected $signature = 'meerkat:validate';
    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Validates the current Meerkat installation and configuration.';

    /**
     * The storage driver validator instance.
     *
     * @var StorageDriverValidator
     */
    private $storageDriverValidator = null;
    /**
     * The solution provider instance.
     *
     * @var SolutionProvider
     */
    private $solutionProvider = null;

    public function __construct(StorageDriverValidator $driverValidator, SolutionProvider $solutions)
    {
        parent::__construct();

        $this->storageDriverValidator = $driverValidator;
        $this->solutionProvider = $solutions;
        $this->solutionProvider->setIsCli(true);
    }

    /**
     * Invokes the required actions for this command.
     */
    public function handle()
    {
        $this->printHeader($this->trans('commands.validate_header'));
        $this->line($this->trans('commands.validate_description'));
        $this->line('');

        $this->validateStorageDrivers();
    }

    /**
     * Utility method to print a boxed header to the console.
     *
     * @param string $text The header text to print.
     * @param int $length The length of the header line. Default 100 characters.
     */
    private function printHeader($text, $length = 100)
    {
        $this->line('');
        $this->line(str_repeat('=', $length));

        $headerLen = mb_strlen($text);
        $availableWidth = $length;
        $leftPad = abs(floor(($availableWidth / 2) - ($headerLen / 2))) - 5;
        $rightPad = $availableWidth - $leftPad - $headerLen - 10;

        $this->line(str_repeat('=', 5) . str_repeat(' ', $leftPad) . $text . str_repeat(' ', $rightPad) . str_repeat('=', 5));

        $this->line(str_repeat('=', $length));
        $this->line('');
    }

    /**
     * Validates the thread and comment storage drivers.
     */
    private function validateStorageDrivers()
    {
        $driverResults = $this->storageDriverValidator->validate();

        $this->printHeader($this->trans('commands.validate_storage_drivers'));

        $this->line('Configured storage path: ' . PathProvider::normalize($driverResults->getDataAttribute('configured_storage_path')));
        $this->line('Using storage path: ' . PathProvider::normalize($driverResults->getDataAttribute('using_storage_path')));


        if ($driverResults->isValid) {
            $this->info($this->trans('commands.validate_storage_valid'));
        } else {
            $this->displayReasons($driverResults->reasons);
        }

        if ($driverResults->hasDataAttribute('driver_configuration')) {

            $this->printHeader($this->trans('commands.validate_driver_configuration'));

            $drivers = $driverResults->getDataAttribute('driver_configuration');

            try {
                /** @var ThreadStorageManagerContract $threadDriver */
                $threadDriver = app()->make($drivers['threads']);

                $threadValidationResults = $threadDriver->validate();

                if ($threadValidationResults->isValid) {
                    $this->info($this->trans('commands.validate_thread_valid'));
                } else {
                    $this->displayReasons($threadValidationResults->reasons);
                }
            } catch (\Exception $e) {
                $this->line($this->trans('commands.validate_error_code', [
                        'errorcode' => Errors::DRIVER_THREAD_CANNOT_USE
                    ]) . ':: ' . $e->getMessage());
            }

        }
    }

    /**
     * Displays the validation failure reasons in the console.
     *
     * @param array $reasons Validation failure reasons.
     */
    private function displayReasons($reasons)
    {
        foreach ($reasons as $failureReason) {
            $errorCode = $failureReason['code'];

            $this->line('');
            $this->line(str_repeat('=', 100));

            $this->error($this->trans('commands.validate_error_code', [
                'errorcode' => $errorCode
            ]));

            $this->line($failureReason['msg']);

            $helpText = $this->solutionProvider->findSolution($errorCode);

            if ($helpText !== null && mb_strlen(trim($helpText)) > 0) {
                $this->info($this->trans('commands.validate_possible_solution', [
                    'errorcode' => $errorCode
                ]));
                $this->info(str_repeat('=', 100));
                $this->line('');
                $this->info($helpText);
            }
        }
    }

}
