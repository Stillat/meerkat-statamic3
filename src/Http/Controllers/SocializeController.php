<?php

namespace Stillat\Meerkat\Http\Controllers;

use Illuminate\Http\Concerns\InteractsWithInput;
use Illuminate\Support\MessageBag;
use Statamic\Http\Controllers\Controller;
use Statamic\Support\Arr;
use Stillat\Meerkat\Exceptions\FormValidationException;
use Stillat\Meerkat\Exceptions\RejectSubmissionException;
use Stillat\Meerkat\Forms\FormHandler;
use Stillat\Meerkat\Forms\MeerkatForm;
use Stillat\Meerkat\Forms\MockSubmission;

/**
 * Class SocializeController
 *
 * Accepts new comment submission requests.
 *
 * @package Stillat\Meerkat\Http\Controllers
 * @since 2.0.0
 */
class SocializeController extends Controller
{
    use InteractsWithInput;

    /**
     * The form handler instance.
     *
     * @var FormHandler
     */
    private $formHandler = null;

    public function __construct(FormHandler $handler)
    {
        $this->formHandler = $handler;
    }

    public function postSocialize()
    {
        $this->formHandler->setData(collect(request()->all()));

        try {
            $this->formHandler->handleRequest();

            $eventResults = $this->runStatamicCreatingEvent($this->formHandler->getSubmissionData());

            if (array_key_exists('errors', $eventResults)) {
                if (is_array($eventResults['errors']) && count($eventResults['errors']) > 0) {
                    return $this->formFailure(
                        $this->formHandler->getSubmissionParameters(),
                        $eventResults['errors'],
                        $this->formHandler->blueprintName()
                    );
                }
            }

        } catch (FormValidationException $validationException) {
            return $this->formFailure(
                $this->formHandler->getSubmissionParameters(),
                $validationException->getErrors(),
                $this->formHandler->blueprintName()
            );
        } catch (RejectSubmissionException $rejectSubmissionException) {
            return $this->formSuccess(
                $this->formHandler->getSubmissionParameters(),
                $this->formHandler->getSubmissionData()
            );
        }
    }

    /**
     * Runs Statamic's form submission creating event to allow
     * other Statamic addon's to intercept the submission.
     *
     * @param array $data The submission data.
     * @return array
     * @throws \Statamic\Exceptions\PublishException
     */
    private function runStatamicCreatingEvent($data)
    {
        $errors = [];
        $mockedSubmission = new MockSubmission();

        $mockedSubmission->data($data);

        $responses = event('Form.submission.creating', $mockedSubmission);

        foreach ($responses as $response) {
            if (!is_array($response)) {
                continue;
            }

            if ($responseErrors = array_get($response, 'errors')) {
                $errors = array_merge($responseErrors, $errors);
                continue;
            }

            $mockedSubmission = array_get($response, 'submission');
        }

        return [
            'errors' => $errors,
            'submission' => $mockedSubmission
        ];
    }

    private function formFailure($params, $errors, $meerkatBlueprint)
    {
        if (request()->ajax()) {
            return response([
                'errors' => (new MessageBag($errors))->all(),
            ], 400);
        }

        $redirect = Arr::get($params, MeerkatForm::KEY_PARAM_ERROR_REDIRECT);

        $response = $redirect ? redirect($redirect) : back();

        return $response->withInput()->withErrors(
            $errors,
            MeerkatForm::getFormSessionHandle($meerkatBlueprint)
        );
    }

    private function formSuccess($params, $data)
    {
        if (request()->ajax()) {
            return response([
                'success' => true,
                'submission' => $data
            ]);
        }

        $redirect = Arr::get($params, '_redirect');

        $response = $redirect ? redirect($redirect) : back();

        // TODO: Refactor to get Meerkat's session name.
        // session()->flash("form.{$submission->form()->handle()}.success", __('Submission successful.'));
        // session()->flash('submission', $submission);

        return $response;
    }

}