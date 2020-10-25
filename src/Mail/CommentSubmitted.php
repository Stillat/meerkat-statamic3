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

    public function build()
    {
        return $this->subject($this->trans('email.subject'))
            ->from($this->comment->getAuthor()->getEmailAddress())->view('meerkat::emails.submission');
    }

}
