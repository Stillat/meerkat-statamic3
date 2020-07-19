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
     * The FormattingConfiguration instance.
     *
     * @var FormattingConfiguration
     */
    private $formatConfiguration = null;

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
     * Indicates if comments created by an authenticated
     * system user should be marked as "published".
     *
     * @var boolean
     */
    public $autoPublishAuthenticatedPosts = false;

    /**
     * Indicates if comments created by an anonymous
     * user should be marked as "published=true".
     * @var bool
     */
    public $autoPublishAnonymousPosts = false;

    /**
     * Specifies the storage directory that should be used
     * to persist threads and their associated comments.
     *
     * @var string
     */
    public $storageDirectory = '';

    /**
     * The directory separator character to use when constructing storage paths.
     *
     * @var string
     */
    public $directorySeparator = DIRECTORY_SEPARATOR;

    /**
     * The comment length limit. If this limit is reached when
     * reading the comment's content data will be truncated.
     *
     * @var int
     */
    public $hardCommentLengthCap = 5000;

}
