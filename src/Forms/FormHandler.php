<?php

namespace Stillat\Meerkat\Forms;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Validation\Factory;
use Statamic\Facades\Entry;
use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;
use Statamic\Fields\Field;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Comments\CommentManager;
use Stillat\Meerkat\Core\Comments\IdRetriever;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentFactoryContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContract;
use Stillat\Meerkat\Core\Threads\Thread;
use Stillat\Meerkat\Core\Threads\ThreadManager;
use Stillat\Meerkat\Exceptions\FormValidationException;
use Stillat\Meerkat\Exceptions\RejectSubmissionException;

/**
 * Class FormHandler
 *
 * Handles Meerkat form submissions.
 *
 * @since 2.0.0
 */
class FormHandler
{
    use UsesConfig;

    const CONFIG_HONEYPOT = 'publishing.honeypot';

    /**
     * A collection of the request data.
     *
     * @var Collection
     */
    protected $data = null;

    /**
     * The blueprint repository implementation instance.
     *
     * @var BlueprintRepository|null
     */
    protected $blueprints = null;

    /**
     * The blueprint being used.
     *
     * @var Blueprint|null
     */
    protected $blueprint = null;

    /**
     * A mapping between the blueprint field names and their configuration.
     *
     * @var array|null
     */
    protected $fieldConfig = null;

    /**
     * The thread manager implementation instance.
     *
     * @var ThreadManager|null
     */
    protected $threadManager = null;

    /**
     * The CommentFactoryContract implementation instance.
     *
     * @var CommentFactoryContract
     */
    protected $commentFactory = null;

    /**
     * The CommentManager instance.
     *
     * @var CommentManager
     */
    protected $commentManager = null;

    public function __construct(
        BlueprintRepository $blueprintRepository,
        ThreadManager $manager,
        CommentManager $commentManager,
        CommentFactoryContract $commentFactory)
    {
        $this->blueprints = $blueprintRepository;
        $this->threadManager = $manager;
        $this->commentManager = $commentManager;
        $this->commentFactory = $commentFactory;
    }

    /**
     * Sets the form's request data.
     *
     * @param  Collection  $data The request data.
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Gets the name of the blueprint being used.
     *
     * @return string
     */
    public function blueprintName()
    {
        return $this->data->get(MeerkatForm::KEY_MEERKAT_FORM, Addon::CODE_ADDON_NAME);
    }

    /**
     * @throws FormValidationException
     * @throws RejectSubmissionException
     */
    public function handleRequest()
    {
        $this->checkHoneypot();
        $this->validate();
    }

    /**
     * Checks the submission data for honeypot fields.
     *
     * @throws RejectSubmissionException
     */
    public function checkHoneypot()
    {
        $honeypotField = $this->getConfig('publishing.honeypot', null);

        if ($honeypotField !== null) {
            if ($this->data->has($honeypotField)) {
                $value = $this->data->get($honeypotField);

                if ($value !== null) {
                    throw new RejectSubmissionException();
                }
            }
        }
    }

    /**
     * Validates the submission data.
     *
     * @return array
     *
     * @throws FormValidationException
     */
    public function validate()
    {
        $rules = [];
        $attributes = [];
        $submissionData = $this->getSubmissionData();

        // Merge in field rules
        foreach ($this->getFields() as $field_name => $field_config) {
            $field_rules = Arr::get($field_config, MeerkatForm::KEY_FORM_CONFIG_VALIDATE);

            if ($field_rules) {
                if (is_string($field_rules)) {
                    $field_rules = explode('|', $field_rules);
                }

                if (is_array($field_rules)) {
                    if (in_array('sometimes', $field_rules)) {
                        if (array_key_exists($field_name, $submissionData) && $submissionData[$field_name] !== null) {
                            $rules[$field_name] = $field_rules;
                        }
                    } else {
                        $rules[$field_name] = $field_rules;
                    }
                }
            }

            // Makes the names prettier.
            $attributes[$field_name] = Arr::get(
                $field_config,
                MeerkatForm::KEY_FORM_CONFIG_DISPLAY_NAME, $field_name
            );
        }

        /** @var Factory $validatorFactory */
        $validatorFactory = app('validator');

        $validator = $validatorFactory->make($submissionData, $rules, [], $attributes);

        if ($validator->fails()) {
            $validationException = new FormValidationException();

            $validationException->setErrors($validator->errors()->toArray());

            throw $validationException;
        }

        return $this->data->all();
    }

    /**
     * Gets the blueprint field configuration.
     *
     * @return array
     */
    private function getFields()
    {
        if ($this->fieldConfig === null) {
            $blueprintFields = $this->getBlueprint($this->data)->fields()->all();

            /** @var Field $field */
            foreach ($blueprintFields as $field) {
                $this->fieldConfig[$field->handle()] = $field->config();
            }
        }

        return $this->fieldConfig;
    }

    /**
     * @param  Collection  $data Submission data.
     * @return Blueprint
     */
    private function getBlueprint($data)
    {
        if ($this->blueprint === null) {
            $blueprintName = $data->get(MeerkatForm::KEY_MEERKAT_FORM, Addon::CODE_ADDON_NAME);

            $this->blueprint = $this->blueprints->find($blueprintName);
        }

        return $this->blueprint;
    }

    /**
     * Gets the form submission data to validate.
     *
     * @return array
     */
    public function getSubmissionData()
    {
        $submissionData = $this->data->filter(function ($value, $key) {
            return Str::startsWith($key, '_') === false;
        })->all();

        $honeypotField = $this->getConfig(self::CONFIG_HONEYPOT, null);

        if ($honeypotField !== null && array_key_exists($honeypotField, $submissionData)) {
            unset($submissionData[$honeypotField]);
        }

        return $submissionData;
    }

    /**
     * Gets the context's associated data.
     *
     * @return array
     */
    public function getEntryData()
    {
        $params = $this->getSubmissionParameters();

        if (array_key_exists(MeerkatForm::KEY_MEERKAT_CONTEXT, $params)) {
            $entry = Entry::find($params[MeerkatForm::KEY_MEERKAT_CONTEXT]);

            if ($entry !== null) {
                $entryId = $entry->id();
                $entryUrl = url($entry->url());

                return [
                    CommentContract::KEY_PAGE_URL => $entryUrl,
                ];
            }
        }

        return [];
    }

    /**
     * Gets the form submission parameters.
     *
     * @return array
     */
    public function getSubmissionParameters()
    {
        return $this->data->filter(function ($value, $key) {
            return Str::startsWith($key, '_') === true;
        })->all();
    }

    /**
     * Attempts to save the Meerkat submission.
     *
     * @param  array  $data The data to save.
     * @return bool
     */
    public function store($data)
    {
        $newCommentId = strval(time());
        $data = [CommentContract::KEY_ID => $newCommentId] + $data;

        $threadId = $this->getThreadId();

        if ($threadId === null) {
            return false;
        }

        /** @var ThreadContract $thread */
        $thread = null;

        if ($this->threadManager->existsForContext($threadId, true)) {
            $thread = $this->threadManager->findById($threadId);
        } else {
            $thread = new Thread();
            $thread->setId($threadId);

            $thread = $this->threadManager->create($thread);
        }
        $replyData = IdRetriever::getIdAndValidateExistence($data);

        unset($data[IdRetriever::KEY_IDS]);

        if ($replyData[IdRetriever::KEY_IS_REPLYING]) {
            return $this->commentManager->saveReplyTo(
                $replyData[CommentContract::KEY_ID],
                $this->commentFactory->makeComment($data)
            );
        }

        return $thread->attachNewComment($this->commentFactory->makeComment($data));
    }

    /**
     * Attempts to locate the thread's string identifier.
     *
     * @return string|null
     */
    public function getThreadId()
    {
        $params = $this->getSubmissionParameters();

        if (array_key_exists(MeerkatForm::KEY_MEERKAT_CONTEXT, $params)) {
            return $params[MeerkatForm::KEY_MEERKAT_CONTEXT];
        }

        return null;
    }
}
