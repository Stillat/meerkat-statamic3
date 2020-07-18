<?php

namespace Stillat\Meerkat\Console\Commands;

use Illuminate\Console\Command;
use Stillat\Meerkat\Core\Contracts\Storage\ThreadStorageManagerContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Feedback\SolutionProvider;
use Stillat\Meerkat\PathProvider;
use Stillat\Meerkat\Validation\StorageDriverValidator;

class ValidateCommand extends Command
{

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
        $this->printHeader('Meerkat Installation Validator');
        $this->line($this->description);
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
        $leftPad = abs(floor( ($availableWidth / 2) - ($headerLen / 2))) - 5;
        $rightPad = $availableWidth - $leftPad - $headerLen - 10;

        $this->line(str_repeat('=', 5).str_repeat(' ', $leftPad).$text.str_repeat(' ', $rightPad).str_repeat('=', 5));

        $this->line(str_repeat('=', $length));
        $this->line('');
    }

    /**
     * Validates the thread and comment storage drivers.
     */
    private function validateStorageDrivers()
    {
        $driverResults = $this->storageDriverValidator->validate();

        $this->printHeader('Validating Storage Drivers');

        $this->line('Configured storage path: ' . PathProvider::normalize($driverResults->getDataAttribute('configured_storage_path')));
        $this->line('Using storage path: ' . PathProvider::normalize($driverResults->getDataAttribute('using_storage_path')));


        if ($driverResults->isValid) {
            $this->info('No issues discovered with storage driver configuration.');
        } else {
            $this->displayReasons($driverResults->reasons);
        }

        if ($driverResults->hasDataAttribute('driver_configuration')) {

            $this->printHeader('Checking Driver Configuration');

            $drivers = $driverResults->getDataAttribute('driver_configuration');

            try {
                /** @var ThreadStorageManagerContract $threadDriver */
                $threadDriver = app()->make($drivers['threads']);

                $threadValidationResults = $threadDriver->validate();

                if ($threadValidationResults->isValid) {
                    $this->info('Thread driver configuration valid.');
                } else {
                    $this->displayReasons($threadValidationResults->reasons);
                }
            } catch (\Exception $e) {
                $this->line('Error Code: ' . Errors::DRIVER_THREAD_CANNOT_USE . ':: ' . $e->getMessage());
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

            $this->error('Error Code: '.$errorCode);
            $this->line($failureReason['msg']);

            $helpText = $this->solutionProvider->findSolution($errorCode);

            if ($helpText !== null && mb_strlen(trim($helpText)) > 0) {
                $this->info('A possible solution for: ' . $errorCode);
                $this->info(str_repeat('=', 100));
                $this->line('');
                $this->info($helpText);
            }
        }
    }

}