<?php

namespace Stillat\Meerkat\Core;

/**
 * Class Configuration
 *
 * The main Meerkat configuration entry point
 *
 * The Configuration class defines and manages various
 * configuration options that change the behavior
 * of the Meerkat Core processes and actions.
 *
 * @since 2.0.0
 */
class Configuration extends ConfigurationContainer
{
    /**
     * Indicates if comments created by an authenticated
     * system user should be marked as "published".
     *
     * @var bool
     */
    public $autoPublishAuthenticatedPosts = false;

    /**
     * Indicates if comments created by an anonymous
     * user should be marked as "published=true".
     *
     * @var bool
     */
    public $autoPublishAnonymousPosts = false;

    /**
     * The number of days after which comments become disabled.
     *
     * Use "0" to never disable comments.
     *
     * @var int
     */
    public $disableCommentsAfterDays = 0;

    /**
     * Specifies the storage directory that should be used
     * to persist threads and their associated comments.
     *
     * @var string
     */
    public $storageDirectory = '';

    /**
     * Specifies the storage directory that should be used
     * to store data for the comment indexes and caches.
     *
     * @var string
     */
    public $indexDirectory = '';

    /**
     * Specifies the storage directory that should be used
     * to store data related to long running tasks.
     *
     * @var string
     */
    public $taskDirectory = '';

    /**
     * Indicates if only comments from authenticated users will be accepted.
     *
     * @var bool
     */
    public $onlyAcceptCommentsFromAuthenticatedUser = false;

    /**
     * Indicates if Meerkat Core should run in debug mode.
     *
     * @var bool
     */
    public $debugMode = false;

    /**
     * Indicates if Meerkat Core should trace third-party interactions with Meerkat Core.
     *
     * Requires Debug Mode.
     *
     * @var bool
     */
    public $debugTracing = false;

    /**
     * The directory separator character to use when constructing storage paths.
     *
     * @var string
     */
    public $directorySeparator = DIRECTORY_SEPARATOR;

    /**
     * The default directory permissions to utilize when creating directories below the web root.
     *
     * @var int
     */
    public $directoryPermissions = 777;

    /**
     * The default file permissions to utilize.
     *
     * @var int
     */
    public $filePermissions = 644;

    /**
     * Indicates if Meerkat should attempt to automatically send emails on new comment submissions.
     *
     * @var bool
     */
    public $sendEmails = false;

    /**
     * An optional "from" email address that will be used instead of the comment author email address.
     *
     * @var string|null
     */
    public $emailFromAddress = null;

    /**
     * Indicates if Meerkat should only send emails not identified as spam.
     *
     * @var bool
     */
    public $onlySendEmailIfNotSpam = true;

    /**
     * A list of emails to send automated submission emails to.
     *
     * @var array
     */
    public $addressToSendEmailTo = [];

    /**
     * Indicates if a full, or off-spec, parser should be used for comment prototype parsing.
     *
     * If all comments of a site adhere to Meerkat's recommended structure, this can be
     * set to `true` to receive comment parsing and loading performance improvements.
     *
     * Leave this set to `false` if you are doing weird things with your data,
     * or are having issues with migrating comments from old Meerkat versions.
     *
     * @since 2.1.6
     *
     * @var bool
     */
    public $useSlimCommentPrototypeParser = false;

    /**
     * The comment length limit. If this limit is reached when
     * reading the comment's content data will be truncated.
     *
     * @var int
     */
    public $hardCommentLengthCap = 5000;

    /**
     * Indicates if Meerkat should track changes to comment data.
     *
     * @var bool
     */
    public $trackChanges = true;

    /**
     * The string value to use when a comment's content cannot be found.
     *
     * @var string
     */
    public $supplementMissingContent = '[Content Missing]';

    /**
     * The string value to use when a comment's email address cannot be found.
     *
     * @var string
     */
    public $supplementAuthorEmail = '[Email Missing]';

    /**
     * The string value to use when a comment's name cannot be found.
     *
     * @var string
     */
    public $supplementAuthorName = '[Name Missing]';

    /**
     * The FormattingConfiguration instance.
     *
     * @var FormattingConfiguration
     */
    private $formatConfiguration = null;

    /**
     * The DataPrivacyConfiguration instance.
     *
     * @var DataPrivacyConfiguration
     */
    private $dataPrivacyConfiguration = null;

    /**
     * A list of searchable comment attributes.
     *
     * @var string[]
     */
    public $searchableAttributes = [

    ];

    public function __construct()
    {
        $this->formatConfiguration = new FormattingConfiguration;
        $this->dataPrivacyConfiguration = new DataPrivacyConfiguration;
    }

    /**
     * Sets the active data privacy configuration.
     *
     * @param  DataPrivacyConfiguration  $config The configuration.
     *
     * @since 2.1.14
     */
    public function setDataPrivacyConfiguration(DataPrivacyConfiguration $config)
    {
        $this->dataPrivacyConfiguration = $config;
    }

    /**
     * Returns access to the active data privacy configuration.
     *
     * @return DataPrivacyConfiguration
     *
     * @since 2.1.14
     */
    public function getDataPrivacyConfiguration()
    {
        return $this->dataPrivacyConfiguration;
    }

    /**
     * Sets the formatting configuration instance.
     *
     * @param  FormattingConfiguration  $formattingConfiguration The configuration instance.
     */
    public function setFormattingConfiguration(FormattingConfiguration $formattingConfiguration)
    {
        $this->formatConfiguration = $formattingConfiguration;
    }

    /**
     * Returns access to the formatting configuration instance.
     *
     * @return FormattingConfiguration
     */
    public function getFormattingConfiguration()
    {
        return $this->formatConfiguration;
    }

    /**
     * Indicates if comment submissions can be disabled.
     *
     * @return bool
     */
    public function commentsCanBeDisabled()
    {
        if (is_int($this->disableCommentsAfterDays)) {
            if ($this->disableCommentsAfterDays === 0) {
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Indicates if comment change tracking is enabled.
     *
     * @return bool
     */
    public function isTrackingChanges()
    {
        return $this->trackChanges;
    }

    /**
     * Disables comment change tracking.
     */
    public function asNoTracking()
    {
        $this->trackChanges = false;
    }
}
