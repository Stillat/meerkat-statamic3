<?php

namespace Stillat\Meerkat\Core\Guard;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\DataObjectContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\DataObject;
use Stillat\Meerkat\Core\Guard\Providers\AkismetSpamGuard;

/**
 * Class Specimen
 *
 * Provides a wrapper around the DataObject to help construct Spam data objects.
 *
 * This wrapper will apply the correct attribute names for internal spam guards,
 * comment and author impersonation, as well as those required for Akismet.
 *
 * @since 2.2.0
 */
class Specimen implements DataObjectContract
{
    use DataObject;

    /**
     * The data attributes for this spam specimen.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Replaces all current data attributes with the provided data.
     *
     * @param  array  $data The data attributes to set
     * @return $this
     */
    public function withData($data)
    {
        $this->setDataAttributes($data);

        return $this;
    }

    /**
     * Sets a data attribute and returns the current specimen instance.
     *
     * @param  string  $key The attribute name to set.
     * @param  string  $value The value to set.
     * @return $this
     */
    private function setAndReturn($key, $value)
    {
        $this->setDataAttribute($key, $value);

        return $this;
    }

    /**
     * Sets the specimen's IP Address.
     *
     * @param  string  $address The IP Address to set.
     * @return Specimen
     */
    public function ipAddress($address)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_USER_IP, $address)
            ->setAndReturn(AuthorContract::KEY_USER_IP, $address);
    }

    /**
     * Sets the requesting site's homepage URL or front page.
     *
     * @param  string  $blog The blog's front page, or home URL.
     * @return $this
     */
    public function blog($blog)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_BLOG, $blog);
    }

    /**
     * Sets the specimen's user agent string.
     *
     * @param  string  $userAgent The user agent.
     * @return $this
     */
    public function userAgent($userAgent)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_USER_AGENT, $userAgent);
    }

    /**
     * Sets the HTTP referer request value.
     *
     * @param  string  $referrer The HTTP referer.
     * @return Specimen
     */
    public function referrer($referrer)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_REFERRER, $referrer)
            ->setAndReturn(CommentContract::KEY_REFERRER, $referrer);
    }

    /**
     * Sets the permalink of the submission's entry.
     *
     * @param  string  $permalink The permalink.
     * @return $this
     */
    public function permalink($permalink)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_PERMALINK, $permalink);
    }

    /**
     * Sets the specimen's author name.
     *
     * @param  string  $authorName The author name.
     * @return Specimen
     */
    public function authorName($authorName)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_COMMENT_AUTHOR, $authorName)
            ->setAndReturn(AuthorContract::KEY_NAME, $authorName);
    }

    /**
     * Sets the specimen's email address.
     *
     * @param  string  $authorEmail The email address.
     * @return Specimen
     */
    public function authorEmail($authorEmail)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_AUTHOR_EMAIL, $authorEmail)
            ->setAndReturn(AuthorContract::KEY_EMAIL_ADDRESS, $authorEmail);
    }

    /**
     * Sets the specimen's author URL.
     *
     * @param  string  $authorUrl The author's URL.
     * @return $this
     */
    public function authorUrl($authorUrl)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_AUTHOR_URL, $authorUrl);
    }

    /**
     * Sets the specimen's content.
     *
     * @param  string  $content The content.
     * @return Specimen
     */
    public function commentContent($content)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_COMMENT_CONTENT, $content)
            ->setAndReturn(CommentContract::KEY_LEGACY_COMMENT, $content)
            ->setAndReturn(CommentContract::KEY_CONTENT, $content)
            ->setAndReturn(CommentContract::INTERNAL_CONTENT_RAW, $content);
    }

    /**
     * Sets the UTC timestamp that the specimen was created.
     *
     * @param  string|int  $commentDateGmt The UTC timestamp.
     * @return $this
     */
    public function commentDateGmt($commentDateGmt)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_COMMENT_DATE_GMT, $commentDateGmt);
    }

    /**
     * Sets the UTC timestamp that the content was created.
     *
     * @param  string|int  $postModifiedGmt The UTC timestamp.
     * @return $this
     */
    public function postModifiedGmt($postModifiedGmt)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_COMMENT_POST_MODIFIED_GMT, $postModifiedGmt);
    }

    /**
     * Sets the ISO 693-1 formatted languages that are used on the site.
     *
     * @param  string  $blogLanguage The blog's language.
     * @return $this
     */
    public function blogLanguage($blogLanguage)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_BLOG_LANGUAGE, $blogLanguage);
    }

    /**
     * Sets the charset being used by comment parameters.
     *
     * Examples include UTF-8 or ISO-8859-1.
     *
     * @param  string  $blogCharset The charset in use by content.
     * @return $this
     */
    public function blogCharset($blogCharset)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_BLOG_CHARSET, $blogCharset);
    }

    /**
     * Sets the user role of the person submitting the specimen.
     *
     * @param  string  $userRole The user's role.
     * @return $this
     */
    public function userRole($userRole)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_USER_ROLE, $userRole);
    }

    /**
     * Sets whether or not the specimen is an Akismet test API request.
     *
     * @param  bool  $isTest Whether or not the request is a test.
     * @return $this
     */
    public function isAkismetTest($isTest)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_IS_TEST, $isTest);
    }

    /**
     * The reason to provide to Akismet if you are rechecking a specimen.
     *
     * @param  string  $recheckReason The reason.
     * @return $this
     */
    public function akismetRecheckReason($recheckReason)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_RECHECK_REASON, $recheckReason);
    }

    /**
     * Sets the honeypot field name, if in use.
     *
     * @param  string  $fieldName The field name.
     * @return $this
     */
    public function akismetHoneypotField($fieldName)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_HONEYPOT_FIELD_NAME, $fieldName);
    }

    /**
     * Indicates the specimen is for a new user account.
     *
     * @return $this
     */
    public function forSignUp()
    {
        return $this->setType(AkismetSpamGuard::TYPE_SIGN_UP);
    }

    /**
     * Indicates the specimen is for a new top-level forum post.
     *
     * @return $this
     */
    public function forForumPost()
    {
        return $this->setType(AkismetSpamGuard::TYPE_FORUM_POST);
    }

    /**
     * Indicates the specimen is a reply to a top-level forum post.
     *
     * @return $this
     */
    public function forForumReply()
    {
        return $this->setType(AkismetSpamGuard::TYPE_REPLY);
    }

    /**
     * Indicates the specimen is a blog post.
     *
     * @return $this
     */
    public function forBlogPost()
    {
        return $this->setType(AkismetSpamGuard::TYPE_BLOG_POST);
    }

    /**
     * Indicates the specimen is from a contact form or feedback form submission.
     *
     * @return $this
     */
    public function forContactForm()
    {
        return $this->setType(AkismetSpamGuard::TYPE_CONTACT_FORM);
    }

    /**
     * Indicates the specimen is a message sent between a few users.
     *
     * @return $this
     */
    public function forMessage()
    {
        return $this->setType(AkismetSpamGuard::TYPE_MESSAGE);
    }

    /**
     * Indicates the specimen is a blog comment.
     *
     * @return $this
     */
    public function forComment()
    {
        return $this->setType(AkismetSpamGuard::TYPE_COMMENT);
    }

    /**
     * Sets the Akismet comment_type parameter.
     *
     * @param  string  $type The type.
     * @return $this
     */
    protected function setType($type)
    {
        return $this->setAndReturn(AkismetSpamGuard::AKISMET_PARAM_COMMENT_TYPE, $type);
    }
}
