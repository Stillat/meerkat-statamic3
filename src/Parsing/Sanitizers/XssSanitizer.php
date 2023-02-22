<?php

namespace Stillat\Meerkat\Parsing\Sanitizers;

use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Parsing\OutputSanitizerContract;
use Stillat\Meerkat\Core\Support\TypeConversions;

/**
 * Class XssSanitizer
 *
 * Removes the configured HTML tags from input values.
 *
 * @since 2.0.0
 */
class XssSanitizer implements OutputSanitizerContract
{
    /**
     * The Meerkat Core configuration instance.
     *
     * @var Configuration
     */
    protected $config = null;

    /**
     * The configured HTML tags to keep.
     *
     * @var string
     */
    protected $tagsToKeep = '';

    public function __construct(Configuration $config)
    {
        $this->config = $config;

        $this->tagsToKeep = implode('', TypeConversions::getArray(
            $this->config->getFormattingConfiguration()->tagsToKeep
        ));
    }

    /**
     * Sanitizes the input value.
     *
     * @param  string  $value The value to sanitize.
     * @return string
     */
    public function sanitize($value)
    {
        if (is_string($value)) {
            return strip_tags($value, $this->tagsToKeep);
        }

        return $value;
    }
}
