<?php

namespace Stillat\Meerkat\Core\Guard\Providers;

use Exception;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Contracts\Http\HttpClientContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardContract;
use Stillat\Meerkat\Core\Errors;
use Stillat\Meerkat\Core\GuardConfiguration;
use Stillat\Meerkat\Core\Logging\ErrorLog;
use Stillat\Meerkat\Core\Logging\ErrorLogContext;
use Stillat\Meerkat\Core\Logging\LocalErrorCodeRepository;
use Stillat\Meerkat\Core\ValidationResult;

/**
 * Class AkismetSpamGuard
 *
 * Uses the Akismet API to determine if a comment contains spam
 *
 * @package Stillat\Meerkat\Core\Guard\Providers
 * @since 2.0.0
 */
class AkismetSpamGuard implements SpamGuardContract
{

    /**
     * The configuration entry key for the Akismet API key.
     */
    const AKISMET_API_KEY = 'akismet_api_key';

    /**
     * The configuration entry key for the Akismet blog home page.
     */
    const AKISMET_HOME_URL = 'akismet_front_page';

    /**
     * The configuration entry key that determines if the API implementation will use HTTP or HTTPS.
     */
    const AKISMET_PROTOCOL_PREFERENCE = 'akismet_use_https';

    /**
     * The configuration entry key that contains the mapping of Meerkat comment fields to API form-param values.
     */
    const AKISMET_FIELD_MAPPINGS = 'akismet_fields';

    /**
     * The configuration entry key that determines the default "spaminess" value on API request failures.
     */
    const AKISMET_DEFAULT_SPAM_VALUE_ON_FAILURE = 'akismet_default_value_on_api_failure';

    /**
     * The configuration entry key that determines if API request are made in "test mode".
     */
    const AKISMET_CONFIG_ENABLE_TEST_MODE = 'akismet_is_test_mode';

    /**
     * The Akismet API endpoint to use when checking if a comment is spam or not.
     */
    const AKISMET_API_CHECK_COMMENT = 'comment-check';

    /**
     * The Akismet API endpoint to use when submitting a specimen as "not spam".
     */
    const AKISMET_API_SUBMIT_HAM = 'submit-ham';

    /**
     * The Akismet API endpoint to use when submitting a specimen as "spam".
     */
    const AKISMET_API_SUBMIT_SPAM = 'submit-spam';

    /**
     * The Akismet API property to specify that the request is being made as a test.
     */
    const AKISMET_API_IS_TEST_MODE = 'is_test';

    /**
     * Indicates if requests to the API should use HTTP or HTTPS.
     *
     * @var boolean
     */
    private $useSsl = true;

    /**
     * The Meerkat configuration instance.
     *
     * @var \Stillat\Meerkat\Core\GuardConfiguration
     */
    private $config = null;

    /**
     * The HTTP Client used to make external web requests.
     *
     * @var \Stillat\Meerkat\Core\Contracts\Http\HttpClientContract
     */
    private $httpClient = null;

    /**
     * Indicates if the spam guard can make external web requests.
     *
     * This value is determined based on user-supplied configuration.
     *
     * @var boolean
     */
    private $canMakeRequests = false;

    /**
     * The Akismet sub-domain to use when issuing API requests.
     *
     * @var string
     */
    private $akismetBaseUrl = '';

    /**
     * Indicates if the API keys have been validated and connections configured.
     *
     * @var boolean
     */
    private $hasBeenValidated = false;

    /**
     * The default value that should be returned when a COMMENT-CHECK API request fails for any reason.
     *
     * The value will indicate the following:
     *     - true:  If the API request fails, the comment will be marked as spam.
     *     - false: If the API request fails, the comment will not be marked as spam.
     * @var boolean
     */
    private $defaultValueOnApiFailure = true;

    /**
     * A mapping of Meerkat form fields to Akismet API fields.
     *
     * @var array
     */
    private $fieldMappings = [];

    /**
     * Determines if API requests will be made using the `is_test` flag.
     *
     * https://akismet.com/development/api/#comment-check
     * https://akismet.com/development/api/#submit-spam
     * https://akismet.com/development/api/#submit-ham
     *
     * @var boolean
     */
    private $isApiTestMode = false;

    /**
     * A collection of errors, if encountered.
     *
     * @var array
     */
    private $errors = [];

    /**
     * Indicates if last operation was a success.
     *
     * @var bool
     */
    protected $success = false;

    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public function getName()
    {
        return 'Akismet';
    }

    /**
     * Gets a value indicating if the detector succeeded.
     *
     * @return boolean
     */
    public function wasSuccess()
    {
        return $this->success;
    }

    /**
     * Creates an instance of AkismetSpamGuard
     *
     * @param \Stillat\Meerkat\Core\GuardConfiguration $config
     */
    public function __construct(GuardConfiguration $config, HttpClientContract $httpClient)
    {
        $this->config = $config;

        $this->httpClient = $httpClient;

        $this->fieldMappings = $this->config->get(AkismetSpamGuard::AKISMET_FIELD_MAPPINGS);
        $this->defaultValueOnApiFailure = $this->config->get(AkismetSpamGuard::AKISMET_DEFAULT_SPAM_VALUE_ON_FAILURE);
        $this->isApiTestMode = $this->config->get(AkismetSpamGuard::AKISMET_CONFIG_ENABLE_TEST_MODE, false);

        // Pre-emptively run validation on configuration values.
        $this->validateAkismetSettings();
    }

    /**
     * Validates the configuration has been configured for the Akismet spam guard.
     *
     * @return \Stillat\Meerkat\Core\ValidationResult
     */
    public function validateAkismetSettings()
    {
        $results = new ValidationResult;

        if (!$this->config->has(AkismetSpamGuard::AKISMET_API_KEY)) {
            $results->reasons[] = [
                'msg' => "A required configuration value 'akismet_api_key' is missing for the Akismet spam guard.",
                'code' => Errors::GUARD_AKISMET_MISSING_API_KEY,
            ];
        }

        if (!$this->config->has(AkismetSpamGuard::AKISMET_HOME_URL)) {
            $results->reasons[] = [
                'msg' => "A required configuration value 'akismet_home_url' is missing for the Akismet spam guard.",
                'code' => Errors::GUARD_AKISMET_MISSING_HOME_URL,
            ];
        }

        if ($this->config->has(AkismetSpamGuard::AKISMET_PROTOCOL_PREFERENCE)) {
            if (boolval($this->config->get(AkismetSpamGuard::AKISMET_PROTOCOL_PREFERENCE, true)) == false) {
                $this->useSsl = false;
            }
        }

        $results->updateValidity();
        $this->canMakeRequests = $results->isValid;

        return $results;
    }

    /**
     * Returns a value indicating if the provided comment has a
     * high probability of being a disingenuous posting.
     *
     * @param DataObjectContract $data
     *
     * @return boolean
     */
    public function getIsSpam(DataObjectContract $data)
    {
        $this->success = false;

        // Check here to prevent calling the API in the constructor.
        if (!$this->checkApiKey()) {
            return;
        }

        if ($this->canMakeRequests) {
            $formData = $this->remapComment($data);

            try {
                $apiResponse = $this->httpClient->post(self::AKISMET_API_CHECK_COMMENT, $formData);

                if ($apiResponse->content !== null) {
                    $responseBody = $apiResponse->content;
                    $this->success = true;

                    if ($responseBody == 'false') {
                        return false;
                    } else if ($responseBody == 'true') {
                        return true;
                    }
                }
            } catch (Exception $generalException) {
                $this->errors[] = $generalException;
            }
        }

        return $this->defaultValueOnApiFailure;
    }

    /**
     * Checks whether the provided API key and blog home URL are valid.
     *
     * @return boolean
     */
    private function checkApiKey()
    {
        $this->success = false;
        $this->validateAkismetSettings();

        if ($this->hasBeenValidated || $this->canMakeRequests == false) {
            return false;
        }

        if ($this->hasBeenValidated == false && $this->canMakeRequests) {
            $validationResults = $this->validateAkismetAPIKey(
                $this->config->get(AkismetSpamGuard::AKISMET_API_KEY),
                $this->config->get(AkismetSpamGuard::AKISMET_HOME_URL));

            if (!$validationResults->isValid) {
                $this->canMakeRequests = false;
                $this->hasBeenValidated = true;

                return false;
            }

            $this->configureHttpClient();
            $this->canMakeRequests = true;
            $this->hasBeenValidated = true;
        }

        $this->success = true;

        return true;
    }

    /**
     * Attempts to validate the Akismet API key with the Akismet service.
     *
     * @param string $apiKey The Akismet API key to check.
     *
     * @return \Stillat\Meerkat\Core\ValidationResult
     */
    private function validateAkismetAPIKey($apiKey, $homePage)
    {
        $this->success = false;

        // https://akismet.com/development/api/#verify-key
        //
        // Key verification is the only Akismet API endpoint that does
        // not use the API as part of the sub-domain. Key verification
        // requires both the API key and the site's home page URL. The
        // API will return a response with either "invalid" or "valid".

        $results = new ValidationResult;

        try {
            $requestData = [
                'key' => $apiKey,
                'blog' => $homePage,
            ];

            $verifyKeyUrl = 'https://rest.akismet.com/1.1/verify-key';

            if ($this->useSsl == false) {
                $verifyKeyUrl = 'http://rest.akismet.com/1.1/verify-key';
            }

            $response = $this->httpClient->post($verifyKeyUrl, $requestData);

            if ($response->content != null) {
                if ($response->content == 'valid') {
                    $results->updateValidity();
                    $this->success = true;

                    return $results;
                } else {
                    $results->reasons[] = [
                        'msg' => 'Could not read response from Akismet API response.',
                        'code' => Errors::GUARD_AKISMET_RESPONSE_FAILURE,
                    ];

                    $logContext = new ErrorLogContext();
                    $logContext->msg = 'Could not read response from Akismet API response.';
                    $logContext->details = $response->content;
                    LocalErrorCodeRepository::log(ErrorLog::make(Errors::GUARD_AKISMET_RESPONSE_FAILURE, $logContext));

                }
            } else {
                $results->reasons[] = [
                    'msg' => 'Could not read response from Akismet API response.',
                    'code' => Errors::GUARD_AKISMET_RESPONSE_FAILURE,
                ];


                $logContext = new ErrorLogContext();
                $logContext->msg = 'Could not read response from Akismet API response.';
                $logContext->details = 'Response content was `null`.';

                LocalErrorCodeRepository::log(ErrorLog::make(Errors::GUARD_AKISMET_RESPONSE_FAILURE, $logContext));
            }
        } catch (Exception $e) {
            $results->reasons[] = [
                'msg' => 'General request failure.',
                'error' => $e,
                'code' => Errors::GUARD_GENERAL_API_REQUEST_FAILURE,
            ];


            $logContext = new ErrorLogContext();
            $logContext->msg = 'An exception was thrown during the API call.';
            $logContext->details = $e->getMessage();

            LocalErrorCodeRepository::log(ErrorLog::make(Errors::GUARD_GENERAL_API_REQUEST_FAILURE, $logContext));

            return $results;
        }

        return $results;
    }

    /**
     * Configures the HTTP client with the Akismet base URLs.
     *
     * @return void
     */
    private function configureHttpClient()
    {
        $apiKey = $this->config->get(AkismetSpamGuard::AKISMET_API_KEY);

        if ($this->useSsl) {
            $this->akismetBaseUrl = "https://{$apiKey}.rest.akismet.com/1.1/";
        } else {
            $this->akismetBaseUrl = "http://{$apiKey}.rest.akismet.com/1.1/";
        }
    }

    /**
     * Remaps the Meerkat comment into the form required by the Akismet API.
     *
     * @param DataObjectContract $data The source data container.
     * @return array
     */
    private function remapComment(DataObjectContract $data)
    {
        $commentAttributes = $data->getDataAttributes();
        $mappedProperties = [];

        foreach ($this->fieldMappings as $key => $value) {
            $sourceValue = null;

            if (array_key_exists($value, $commentAttributes)) {
                $sourceValue = $commentAttributes[$value];
            }

            if ($sourceValue !== null) {
                $mappedProperties[$key] = $sourceValue;
            }
        }

        // Add the 'blob' parameter.
        $mappedProperties['blog'] = $this->config->get('akismet_front_page', '');

        if ($this->isApiTestMode) {
            $mappedProperties[AkismetSpamGuard::AKISMET_API_IS_TEST_MODE] = true;
        }

        return $mappedProperties;
    }

    /**
     * Marks a comment as a spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     * @param DataObjectContract $data
     *
     * @return boolean
     */
    public function markAsSpam(DataObjectContract $data)
    {
        $this->success = false;

        // Check here to prevent calling the API in the constructor.
        if (!$this->checkApiKey()) {
            return false;
        }

        if ($this->canMakeRequests) {
            $formData = $this->remapComment($data);

            // Check if the referrer and user_ip values are available.
            if (array_key_exists('referrer', $formData) == false ||
                array_key_exists('user_up', $formData) == false) {
                return false;
            }

            try {
                $apiResponse = $this->httpClient->post(self::AKISMET_API_SUBMIT_SPAM, $formData);

                if ($apiResponse->content !== null) {
                    if ($apiResponse->content == 'Thanks for making the web a better place.') {
                        $this->success = true;

                        return true;
                    }
                }
            } catch (Exception $generalException) {
                $this->errors[] = $generalException;
            }
        }

        return false;
    }

    /**
     * Marks a comment as not-spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     * @param DataObjectContract $data
     *
     * @return boolean
     */
    public function markAsHam(DataObjectContract $data)
    {
        $this->success = false;

        // Check here to prevent calling the API in the constructor.
        if (!$this->checkApiKey()) {
            return false;
        }

        if ($this->canMakeRequests) {
            $formData = $this->remapComment($data);

            // Check if the referrer and user_ip values are available.
            if (array_key_exists('referrer', $formData) == false ||
                array_key_exists('user_up', $formData) == false) {
                return false;
            }

            try {
                $apiResponse = $this->httpClient->post(self::AKISMET_API_SUBMIT_HAM, $formData);

                if ($apiResponse->content !== null) {
                    if ($apiResponse->content == 'Thanks for making the web a better place.') {
                        $this->success = true;

                        return true;
                    }
                }
            } catch (Exception $generalException) {
                $this->errors[] = $generalException;
            }
        }

        return false;
    }

    /**
     * Returns a value indicating if a guard supports submitting
     * not-spam results to a third-party service or product.
     *
     * @return boolean
     */
    public function supportsSubmittingHam()
    {
        return true;
    }

    /**
     * Returns a value indicating if a guard supports submitting
     * spam results to a third-party service or product.
     *
     * @return boolean
     */
    public function supportsSubmittingSpam()
    {
        return true;
    }

    /**
     * Returns a value indicating if the guard encountered errors.
     *
     * @return boolean
     * @since 2.0.0
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * Returns a collection of errors, if available.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
