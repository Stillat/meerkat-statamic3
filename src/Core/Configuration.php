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
 * @package Stillat\Meerkat\Core
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
     * The FormattingConfiguration instance.
     *
     * @var FormattingConfiguration
     */
    private $formatConfiguration = null;

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
    }

    /**
     * Sets the formatting configuration instance.
     *
     * @param FormattingConfiguration $formattingConfiguration The configuration instance.
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
