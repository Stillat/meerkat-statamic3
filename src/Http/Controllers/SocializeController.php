<?php

namespace Stillat\Meerkat\Http\Controllers;

use Illuminate\Http\Concerns\InteractsWithInput;
use Illuminate\Support\MessageBag;
use Statamic\Http\Controllers\Controller;
use Statamic\Support\Arr;
use Stillat\Meerkat\Core\Comments\Comment;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
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
        $commentData = [];

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

            if (array_key_exists('submission', $eventResults)) {
                if ($eventResults['submission'] !== null) {
                    if ($eventResults['submission'] instanceof MockSubmission) {
                        $commentData = $eventResults['submission']->data();
                    }
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
                $this->formHandler->getSubmissionData(),
                $this->formHandler->blueprintName()
            );
        }

        $commentData = $this->fillWithRequestData($commentData);
        $commentData = $this->fillWithUserData($commentData);
        $commentData = $this->fillWithEntryData($commentData);

        $didStore = $this->formHandler->store($commentData);

        if ($didStore) {
            return $this->formSuccess(
                $this->formHandler->getSubmissionParameters(),
                $this->formHandler->getSubmissionData(),
                $this->formHandler->blueprintName()
            );
        }

        return $this->formFailure(
            $this->formHandler->getSubmissionParameters(),
            [],
            $this->formHandler->blueprintName()
        );
    }

    /**
     * Fills the comment data with the context's information.
     *
     * @param array $data The comment's data.
     * @return array
     */
    private function fillWithEntryData($data)
    {
        return array_merge($data, $this->formHandler->getEntryData());
    }

    /**
     * Adds the current authenticated user information, if the email addresses match.
     *
     * @param array $data The comment's data.
     * @return array
     */
    private function fillWithUserData($data)
    {
        $currentUser = auth()->user();

        if ($currentUser === null) {
            return $data;
        }

        if ($data[AuthorContract::KEY_EMAIL_ADDRESS] === $currentUser->email()) {
            $data[AuthorContract::AUTHENTICATED_USER_ID] = $currentUser->getAuthIdentifier();
        }

        return $data;
    }

    /**
     * Adds the request information (such as User-Agent) to the comment's data.
     *
     * @param array $data The comment data.
     * @return array
     */
    private function fillWithRequestData($data)
    {
        $requestData = [
            AuthorContract::KEY_USER_AGENT => request()->header('User-Agent'),
            AuthorContract::KEY_USER_IP => request()->getClientIp(),
            CommentContract::KEY_REFERRER => request()->server('HTTP_REFERER'),
        ];

        return array_merge($data, $requestData);
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

    private function formSuccess($params, $data, $meerkatBlueprint)
    {
        if (request()->ajax()) {
            return response([
                'success' => true,
                'submission' => $data
            ]);
        }

        $redirect = Arr::get($params, '_redirect');

        $response = $redirect ? redirect($redirect) : back();

        $mockSubmission = new MockSubmission();
        $mockSubmission->data($data);

        session()->flash(MeerkatForm::getFormSessionHandle($meerkatBlueprint).'.success', __('Submission successful.'));
        session()->flash('submission', $mockSubmission);

        return $response;
    }

}