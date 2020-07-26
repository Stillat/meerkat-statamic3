<?php

namespace Stillat\Meerkat\Forms;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory;
use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;
use Statamic\Fields\Field;
use Stillat\Meerkat\Addon;
use Stillat\Meerkat\Concerns\UsesConfig;
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

    public function __construct(BlueprintRepository $blueprintRepository)
    {
        $this->blueprints = $blueprintRepository;
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

        // TODO: Save stuffs.
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
        return $this->data->filter(function ($value, $key) {
            return Str::startsWith($key, '_') === false;
        })->all();
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

}