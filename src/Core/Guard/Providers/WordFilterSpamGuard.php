<?php

namespace Stillat\Meerkat\Core\Guard\Providers;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\SpamGuardContract;
use Stillat\Meerkat\Core\Guard\SpamReason;
use Stillat\Meerkat\Core\GuardConfiguration;
use Stillat\Meerkat\Core\Support\Str;

/**
 * Class WordFilterSpamGuard
 *
 * Determines if a comment is spam by checking against a list of unfavorable words
 *
 * @since 2.0.0
 */
class WordFilterSpamGuard implements SpamGuardContract
{
    const WDF_MATCHED = 'WDF-01-001';

    const WDF_DEFAULT_MESSAGE = 'Word filter matched against a configured value.';

    private $reasons = [];

    /**
     * The Meerkat Guard configuration instance.
     *
     * @var GuardConfiguration
     */
    private $guardConfig = null;

    public function __construct(GuardConfiguration $config)
    {
        $this->guardConfig = $config;
    }

    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public static function getConfigName()
    {
        return 'Word Filter';
    }

    /**
     * Gets the name of the spam detector.
     *
     * @return string
     */
    public function getName()
    {
        return 'WordFilter';
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
     * Gets a value indicating if the detector succeeded.
     *
     * @return bool
     */
    public function wasSuccess()
    {
        return true;
    }

    /**
     * Returns a value indicating if the provided comment has a
     * high probability of being a disingenuous posting.
     *
     *
     * @return bool
     */
    public function getIsSpam(DataObjectContract $data)
    {
        if (count($this->guardConfig->bannedWords) === 0) {
            return false;
        }

        $emailAddress = mb_strtolower($data->getDataAttribute(AuthorContract::KEY_EMAIL_ADDRESS));
        $name = mb_strtolower($data->getDataAttribute(AuthorContract::KEY_NAME));
        $content = mb_strtolower($data->getDataAttribute(CommentContract::KEY_LEGACY_COMMENT));
        $contentComment = mb_strtolower($data->getDataAttribute(CommentContract::KEY_CONTENT));
        $contentRaw = mb_strtolower($data->getDataAttribute(CommentContract::INTERNAL_CONTENT_RAW));

        foreach ($this->guardConfig->bannedWords as $word) {
            if (Str::contains($contentRaw, $word)) {
                $reason = new SpamReason();
                $reason->setReasonText(self::WDF_DEFAULT_MESSAGE);
                $reason->setReasonCode(self::WDF_MATCHED);
                $reason->setReasonContext([
                    'word' => $word,
                    'property' => CommentContract::INTERNAL_CONTENT_RAW,
                ]);

                $this->reasons[] = $reason;

                return true;
            }

            if (Str::contains($emailAddress, $word)) {
                $reason = new SpamReason();
                $reason->setReasonText(self::WDF_DEFAULT_MESSAGE);
                $reason->setReasonCode(self::WDF_MATCHED);
                $reason->setReasonContext([
                    'word' => $word,
                    'property' => AuthorContract::KEY_EMAIL_ADDRESS,
                ]);

                $this->reasons[] = $reason;

                return true;
            }

            if (Str::contains($name, $word)) {
                $reason = new SpamReason();
                $reason->setReasonText(self::WDF_DEFAULT_MESSAGE);
                $reason->setReasonCode(self::WDF_MATCHED);
                $reason->setReasonContext([
                    'word' => $word,
                    'property' => AuthorContract::KEY_NAME,
                ]);

                $this->reasons[] = $reason;

                return true;
            }

            if (Str::contains($content, $word)) {
                $reason = new SpamReason();
                $reason->setReasonText(self::WDF_DEFAULT_MESSAGE);
                $reason->setReasonCode(self::WDF_MATCHED);
                $reason->setReasonContext([
                    'word' => $word,
                    'property' => CommentContract::KEY_LEGACY_COMMENT,
                ]);

                $this->reasons[] = $reason;

                return true;
            }

            if (Str::contains($contentComment, $word)) {
                $reason = new SpamReason();
                $reason->setReasonText(self::WDF_DEFAULT_MESSAGE);
                $reason->setReasonCode(self::WDF_MATCHED);
                $reason->setReasonContext([
                    'word' => $word,
                    'property' => CommentContract::KEY_CONTENT,
                ]);

                $this->reasons[] = $reason;

                return true;
            }
        }

        return false;
    }

    /**
     * Marks a comment as a spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     *
     * @return bool
     */
    public function markAsSpam(DataObjectContract $data)
    {
        return false;
    }

    /**
     * Marks a comment as not-spam, and communicates this
     * to third-party vendors if configured to do so.
     *
     *
     * @return bool
     */
    public function markAsHam(DataObjectContract $data)
    {
        return false;
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
        return false;
    }

    /**
     * Returns a collection of errors, if available.
     *
     * @return array
     */
    public function getErrors()
    {
        return [];
    }
}
