<?php

namespace Stillat\Meerkat\Forms;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory;
use Statamic\Facades\Entry;
use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;
use Statamic\Fields\Field;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Concerns\UsesConfig;
use Stillat\Meerkat\Core\Comments\CommentManager;
use Stillat\Meerkat\Core\Comments\IdRetriever;
use Stillat\Meerkat\Core\Comments\Mutation\DelayedMutationManager;
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
 * @package Stillat\Meerkat\Forms
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

    protected $commentFactory = null;

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
     * @param Collection $data The request data.
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
        $this->checkHoneypot();;
        $this->validate();
    }

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
     * @throws FormValidationException
     */
    public function validate()
    {
        $rules = [];
        $attributes = [];

        // Merge in field rules
        foreach ($this->getFields() as $field_name => $field_config) {
            if ($field_rules = array_get($field_config, MeerkatForm::KEY_FORM_CONFIG_VALIDATE)) {
                $rules[$field_name] = $field_rules;
            }

            // Makes the names prettier.
            $attributes[$field_name] = array_get(
                $field_config,
                MeerkatForm::KEY_FORM_CONFIG_DISPLAY_NAME, $field_name
            );
        }

        /** @var Factory $validatorFactory */
        $validatorFactory = app('validator');

        $submissionData = $this->getSubmissionData();

        $validator = $validatorFactory->make($this->getSubmissionData(), $rules, [], $attributes);

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
     * @param Collection $data Submission data.
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
                    CommentContract::KEY_PAGE_URL => $entryUrl
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

    public function store($data)
    {
        $newCommentId = time();
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

        $didSave = $thread->attachNewComment($this->commentFactory->makeComment($data));

        if ($didSave) {
            // TODO: Is this redunant with core managing this?
            app()->terminating(function () use ($newCommentId) {
                /** @var DelayedMutationManager $delayedMutations */
                $delayedMutations = app(DelayedMutationManager::class);

                $delayedMutations->raiseCreated($newCommentId);
            });
        }

        return $didSave;
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
