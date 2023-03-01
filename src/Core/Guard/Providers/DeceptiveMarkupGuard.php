<?php

namespace Stillat\Meerkat\Core\Guard\Providers;

use DOMDocument;
use Exception;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Parsing\MarkdownParserContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardContract;
use Stillat\Meerkat\Core\Guard\SpamReason;
use Stillat\Meerkat\Core\Logging\ExceptionLoggerFactory;

/**
 * Class DeceptiveMarkupGuard
 *
 * Checks user-supplied input for potentially deceptive HTML markup.
 *
 * An example of this would be someone submitting a comment
 * containing nothing but an "invisible" HTML link, etc.
 *
 * @since 2.2.0
 */
class DeceptiveMarkupGuard implements SpamGuardContract
{
    const DMG_LINK_DETECTED = 'DMG-01-001';

    const DMG_DEFAULT_MESSAGE = 'Potentially deceptive markup was detected.';

    /**
     * The MarkdownParserContract implementation instance.
     *
     * @var MarkdownParserContract
     */
    private $markdownParser = null;

    /**
     * A list of all reasons a specimen was marked as spam.
     *
     * @var SpamReason[]
     */
    private $reasons = [];

    /**
     * Indicates if the spam guard ran successfully.
     *
     * @var bool
     */
    private $success = true;

    /**
     * A collection of errors, if encountered.
     *
     * @var array
     */
    private $errors = [];

    public function __construct(MarkdownParserContract $markdownParser)
    {
        $this->markdownParser = $markdownParser;
    }

    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public static function getConfigName()
    {
        return 'Deceptive Markup Detector';
    }

    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public function getName()
    {
        return 'DeceptiveMarkupDetector';
    }

    /**
     * Gets a value indicating if the detector succeeded.
     *
     * @return bool
     */
    public function wasSuccess()
    {
        return $this->success;
    }

    /**
     * Gets the reasons the item was marked as spam.
     *
     * @return SpamReason[]
     */
    public function getSpamReasons()
    {
        return $this->reasons;
    }

    /**
     * Returns a value indicating if the provided object has a
     * high probability of being a disingenuous posting.
     *
     *
     * @return bool
     */
    public function getIsSpam(DataObjectContract $data)
    {
        $emailAddress = mb_strtolower($data->getDataAttribute(AuthorContract::KEY_EMAIL_ADDRESS));
        $name = mb_strtolower($data->getDataAttribute(AuthorContract::KEY_NAME));
        $content = mb_strtolower($data->getDataAttribute(CommentContract::KEY_LEGACY_COMMENT));
        $contentComment = mb_strtolower($data->getDataAttribute(CommentContract::KEY_CONTENT));
        $contentRaw = mb_strtolower($data->getDataAttribute(CommentContract::INTERNAL_CONTENT_RAW));

        $contentMapping = [
            [AuthorContract::KEY_EMAIL_ADDRESS => $emailAddress],
            [AuthorContract::KEY_NAME => $name],
            [CommentContract::KEY_LEGACY_COMMENT => $content],
            [CommentContract::KEY_CONTENT => $contentComment],
            [CommentContract::INTERNAL_CONTENT_RAW => $contentRaw],
        ];

        return $this->checkAllForDeceptiveMarkup($contentMapping);
    }

    /**
     * @param  array  $contentMapping The content/property mapping.
     * @return bool
     */
    private function checkAllForDeceptiveMarkup($contentMapping)
    {
        foreach ($contentMapping as $mapping) {
            $propertyName = array_keys($mapping)[0];
            $contentValue = array_values($mapping)[0];

            if ($this->checkSingleForDeceptiveMarkup($propertyName, $contentValue) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Marks an object as a spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     *
     * @return bool
     */
    public function markAsSpam(DataObjectContract $data)
    {
        return true;
    }

    /**
     * Marks a object as not-spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     *
     * @return bool
     */
    public function markAsHam(DataObjectContract $data)
    {
        return true;
    }

    /**
     * Returns a value indicating if a guard supports submitting
     * not-spam results to a third-party service or product.
     *
     * @return bool
     */
    public function supportsSubmittingHam()
    {
        return false;
    }

    /**
     * Returns a value indicating if a guard supports submitting
     * spam results to a third-party service or product.
     *
     * @return bool
     */
    public function supportsSubmittingSpam()
    {
        return false;
    }

    /**
     * Returns a value indicating if the guard encountered errors.
     *
     * @return bool
     *
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

    /**
     * Checks if a piece of user-supplied content contains deceptive HTML markup.
     *
     * @param  string  $property The property name being checked.
     * @param  string  $content The content to check.
     * @return bool
     */
    private function checkSingleForDeceptiveMarkup($property, $content)
    {
        if (mb_strlen(trim($content)) === 0) {
            return false;
        }

        try {
            $document = new DOMDocument();
            $document->loadHTML($this->markdownParser->parseDocument($content));
            $htmlLinks = $document->getElementsByTagName('a');

            if ($htmlLinks->length === 0) {
                return false;
            }

            for ($i = 0; $i < $htmlLinks->length; $i++) {
                $link = $htmlLinks->item($i);

                if (mb_strlen(trim($link->textContent)) === 0) {
                    $reason = new SpamReason();
                    $reason->setReasonText(self::DMG_DEFAULT_MESSAGE);
                    $reason->setReasonCode(self::DMG_LINK_DETECTED);
                    $reason->setReasonContext([
                        'content' => $content,
                        'property' => $property,
                    ]);

                    $this->reasons[] = $reason;

                    return true;
                }
            }
        } catch (Exception $generalException) {
            ExceptionLoggerFactory::log($generalException);
            $this->errors[] = $generalException;
        }

        return false;
    }
}
