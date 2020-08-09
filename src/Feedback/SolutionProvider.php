<?php

namespace Stillat\Meerkat\Feedback;

use Illuminate\Translation\Translator;
use Statamic\Facades\Antlers;
use Statamic\Markdown\Parser as StatamicMarkdownParser;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\PathProvider;

/**
 * Class SolutionProvider
 *
 * Provides services and utilities for locating help documentation for error codes.
 *
 * Format of document file names:
 *  cli.win.code-<ERROR_CODE>.md for console specific help on Windows
 *  win.code-<ERROR_CODE>.md for non-console help on Windows
 *  cli.code-<ERROR_CODE>.md for console specific help on all systems (superseded by Windows version, if available)
 *  code-<ERROR_CODE>.md for non-console help on all systems (superseded by Windows version, if available)
 *
 * The same naming scheme applies for all locales.
 *
 * @package Stillat\Meerkat\Feedback
 * @since 2.0.0
 */
class SolutionProvider
{

    /**
     * The system's current locale.
     *
     * @var string
     */
    protected $currentLocale = 'en';

    /**
     * The fallback locale to use.
     *
     * @var string
     */
    protected $fallbackLocale = 'en';

    /**
     * Indicates if Meerkat is current running on Windows.
     *
     * @var bool
     */
    protected $isWindows = false;

    /**
     * The operating system prefix to utilize, if any.
     *
     * @var string
     */
    protected $osPrefix = '';

    /***
     * Indicates if the provider is running in the console.
     *
     * @var bool
     */
    protected $isCli = false;

    /**
     * A header that will be prepended if the Antlers template engine fails.
     *
     * @var string
     */
    protected $engineFailureHeader = '';

    /**
     * The Statamic Markdown Parser instance.
     *
     * @var StatamicMarkdownParser|null
     */
    protected $markdownParser = null;

    /**
     * Indicates if a CLI specific version was located.
     *
     * @var bool
     */
    protected $foundCliVersion = false;

    /**
     * The current error code being searched.
     *
     * @var string
     */
    protected $errorCode = '';

    public function __construct(Translator $translator, StatamicMarkdownParser $parser)
    {
        $this->currentLocale = $translator->locale();
        $this->fallbackLocale = $translator->getFallback();
        $this->isWindows = Addon::isWindows();
        $this->markdownParser = $parser;

        if ($this->isWindows) {
            $this->osPrefix = 'win.';
        }

        $currentLocaleFailurePath = PathProvider::getResourcesDirectory('solutions/' . $this->currentLocale . '/provider_engine_failed.md');
        $fallbackLocaleFailurePath = PathProvider::getResourcesDirectory('solutions/' . $this->fallbackLocale . '/provider_engine_failed.md');

        if (file_exists($currentLocaleFailurePath)) {
            $this->engineFailureHeader = file_get_contents($currentLocaleFailurePath);
        }

        if (file_exists($fallbackLocaleFailurePath)) {
            $this->engineFailureHeader = file_get_contents($fallbackLocaleFailurePath);
        }

        // Guard against empty failure headers.
        if (mb_strlen(trim($this->engineFailureHeader)) === 0) {
            $this->engineFailureHeader = 'The solution template engine has failed; some content may not be processed completely. Error Code: 01-009: Host template system failure (Antlers).';
        }
    }

    /**
     * Gets whether the provider is running in the console.
     *
     * @return bool
     */
    public function getIsCli()
    {
        return $this->isCli;
    }

    /**
     * Sets whether the provider is running in the console.
     *
     * @param bool $isCli Whether the provider is running in the console.
     */
    public function setIsCli($isCli)
    {
        $this->isCli = $isCli;
    }

    /**
     * Attempts to locate a help document for the provided error code.
     *
     * @param string $errorCode The error code to get help with.
     * @return string|string[]
     */
    public function findSolution($errorCode)
    {
        $solutionPath = $this->getSolutionPath($errorCode);

        if ($solutionPath !== null) {
            $fileContents = file_get_contents($solutionPath);

            if ($this->isCli && $this->foundCliVersion == false) {
                $fileContents = $this->markdownParser->parse($fileContents);
                $fileContents = strip_tags($fileContents);
            }

            // Protect against something happening and causing the
            // solution provider from crashing out completely.
            try {
                $parsedContents = $this->getContents($fileContents);
            } catch (\Exception $e) {
                $parsedContents = $this->naiveReplaceVars($fileContents);
            }

            return $parsedContents;
        }

        return '';
    }

    /**
     * Attempts to locate a possible solution for the provided error code.
     *
     * @param string $errorCode The error code to get help with.
     * @return string|null
     */
    private function getSolutionPath($errorCode)
    {
        if ($errorCode === null || mb_strlen(trim($errorCode)) === 0) {
            return null;
        }

        $this->errorCode = mb_strtolower($errorCode);
        $this->foundCliVersion = false;

        // Checks if a CLI-specific version has been supplied. If so, use that instead.
        if ($this->isCli) {

            $osFilePath = 'cli.' . $this->osPrefix . 'code-' . $this->errorCode . '.md';
            $defaultFilePath = 'cli.code-' . $this->errorCode . '.md';

            $osCurrentLocalePath = PathProvider::getResourcesDirectory('solutions/' . $this->currentLocale . '/' . $osFilePath);
            $osFallbackLocalePath = PathProvider::getResourcesDirectory('solutions/' . $this->fallbackLocale . '/' . $osFilePath);
            $defaultCurrentLocalePath = PathProvider::getResourcesDirectory('solutions/' . $this->currentLocale . '/' . $defaultFilePath);
            $defaultFallbackLocalePath = PathProvider::getResourcesDirectory('solutions/' . $this->fallbackLocale . '/' . $defaultFilePath);

            if (file_exists($osCurrentLocalePath)) {
                $this->foundCliVersion = true;
                return $osCurrentLocalePath;
            }

            if (file_exists($defaultCurrentLocalePath)) {
                $this->foundCliVersion = true;
                return $defaultCurrentLocalePath;
            }

            if (file_exists($osFallbackLocalePath)) {
                $this->foundCliVersion = true;
                return $osFallbackLocalePath;
            }

            if (file_exists($defaultFallbackLocalePath)) {
                $this->foundCliVersion = true;
                return $defaultFallbackLocalePath;
            }
        }

        $osFilePath = $this->osPrefix . 'code-' . $this->errorCode . '.md';
        $defaultFilePath = 'code-' . $this->errorCode . '.md';

        $osCurrentLocalePath = PathProvider::getResourcesDirectory('solutions/' . $this->currentLocale . '/' . $osFilePath);
        $osFallbackLocalePath = PathProvider::getResourcesDirectory('solutions/' . $this->fallbackLocale . '/' . $osFilePath);
        $defaultCurrentLocalePath = PathProvider::getResourcesDirectory('solutions/' . $this->currentLocale . '/' . $defaultFilePath);
        $defaultFallbackLocalePath = PathProvider::getResourcesDirectory('solutions/' . $this->fallbackLocale . '/' . $defaultFilePath);

        if (file_exists($osCurrentLocalePath)) {
            return $osCurrentLocalePath;
        }

        if (file_exists($defaultCurrentLocalePath)) {
            return $defaultCurrentLocalePath;
        }

        if (file_exists($osFallbackLocalePath)) {
            return $osFallbackLocalePath;
        }

        if (file_exists($defaultFallbackLocalePath)) {
            return $defaultFallbackLocalePath;
        }

        return null;
    }

    /**
     * Process the provider content and returns the results.
     *
     * @param string $content The content to parse.
     * @return string
     */
    private function getContents($content)
    {
        return (string)Antlers::parse($content, $this->getVars());
    }

    /**
     * Gets the variables that should be supplied to provider templates.
     *
     * @return array
     */
    private function getVars()
    {
        return [
            'php_user_name' => get_current_user(),
            'is_cli' => $this->isCli,
            'error_code' => $this->errorCode,
            'environment' => php_uname(),
            'os_name' => PHP_OS,
            'storage_path' => PathProvider::normalize(PathProvider::contentPath()),
            'win_storage_path' => PathProvider::winPath(PathProvider::contentPath()),
            'comment_storage_driver' => config('meerkat.storage.drivers.comments'),
            'thread_storage_driver' => config('meerkat.storage.drivers.threads')
        ];
    }


    /**
     * Provides a fallback if the Antlers system has failed.
     *
     * @param string $content The content to replace values in.
     * @return string|string[]
     */
    private function naiveReplaceVars($content)
    {
        foreach ($this->getVars() as $varName => $varValue) {
            $content = str_replace('{{ ' . $varName . ' }}', $varValue, $content);
        }

        // Adds the notice that the template engine has failed.
        $content = trim($this->engineFailureHeader) . "\n\n" . $content;

        return $content;
    }

}
