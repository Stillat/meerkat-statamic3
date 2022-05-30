<?php

namespace Stillat\Meerkat\Mail;

use Illuminate\Mail\Mailable;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Concerns\UsesTranslations;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Data\FieldMapper;
use Stillat\Meerkat\ExportFields;

/**
 * Class CommentSubmitted
 *
 * Provides data transformations and view data to build comment submission emails.
 *
 * @package Stillat\Meerkat\Mail
 * @since 2.1.5
 */
class CommentSubmitted extends Mailable
{
    use UsesTranslations, UsesConfig;

    /**
     * The comment to send an email for.
     *
     * @var CommentContract|null
     */
    public $comment = null;

    /**
     * The URL to the site's Control Panel.
     *
     * @var string
     */
    public $controlPanelUrl = '';

    /**
     * Indicates whether to show the "View in Control Panel" button.
     *
     * @var bool
     */
    public $showControlPanelButton = true;

    /**
     * The fields to present in the email body.
     *
     * @var array
     */
    public $presentData = [];

    /**
     * The export FieldMapper instance.
     *
     * @var FieldMapper
     */
    private $fieldMapper = null;

    /**
     * An optional "from" address override.
     *
     * @var string|null
     */
    private $fromAddress = null;

    public function __construct(CommentContract $comment, FieldMapper $mapper)
    {
        $this->fieldMapper = $mapper;
        $this->fieldMapper->setUseTextTransform(true);

        $this->fieldMapper->setTextTransformValues(
            $this->trans('fields.transform.true'),
            $this->trans('fields.transform.false')
        );

        $this->controlPanelUrl = url('/' . config('statamic.cp.route') . '/meerkat');
        $this->showControlPanelButton = $this->getConfig('email.show_control_panel_button', true);
        $this->comment = $comment;

        $fields = ExportFields::getExportFields();
        $targetFields = $this->fieldMapper->rewriteFields($fields);
        $exportData = $this->fieldMapper->getData($comment->toArray(), $targetFields);

        $dataToPresent = [];

        for ($i = 0; $i < count($targetFields); $i++) {
            if ($exportData[$i] !== null) {
                $field = $fields[$i];

                $dataToPresent[$this->trans('fields.' . $field)] = $exportData[$i];
            }
        }

        $this->presentData = $dataToPresent;

        unset($fields, $targetFields, $exportData);
    }

    /**
     * Sets an optional "from" email address override.
     *
     * @param string|null $address The address.
     * @return $this
     */
    public function setFromAddress($address)
    {
        $this->fromAddress = $address;

        return $this;
    }

    protected function getFromEmailAddress()
    {
        if (is_string($this->fromAddress) && strlen($this->fromAddress) > 0) {
            return $this->fromAddress;
        }

        return $this->comment->getAuthor()->getEmailAddress();
    }

    public function build()
    {
        return $this->subject($this->trans('email.subject'))
            ->from($this->getFromEmailAddress())->view('meerkat::emails.submission');
    }

}
