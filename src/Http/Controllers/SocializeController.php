<?php

namespace Stillat\Meerkat\Http\Controllers;

use Illuminate\Http\Concerns\InteractsWithInput;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Auth\UserRepository;
use Statamic\Events\FormSubmitted;
use Statamic\Http\Controllers\Controller;
use Statamic\Support\Arr;
use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Identity\AuthorContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
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

    const KEY_SUBMISSION = 'submission';
    const KEY_ERRORS = 'errors';
    const KEY_SUCCESS = 'success';

    /**
     * The form handler instance.
     *
     * @var FormHandler
     */
    private $formHandler = null;

    /**
     * The Meerkat Core configuration container.
     *
     * @var Configuration
     */
    private $coreConfig = null;

    /**
     * @var ThreadManagerContract
     */
    private $manager = null;

    /**
     * Indicates if the currently authenticated user is submitting the current comment.
     *
     * @var bool
     */
    private $currentUserIsPublishingComment = false;

    public function __construct(FormHandler $handler, Configuration $coreConfig, ThreadManagerContract $manager)
    {
        $this->formHandler = $handler;
        $this->coreConfig = $coreConfig;
        $this->manager = $manager;
    }

    /**
     * Handle a form submission request.
     *
     * @return mixed
     */
    public function postSocialize()
    {
        if ($this->coreConfig->onlyAcceptCommentsFromAuthenticatedUser === true) {
            /** @var UserRepository $statamicUserRepository */
            $statamicUserRepository = app(UserRepository::class);

            if ($statamicUserRepository->current() === null) {
                if (request()->ajax()) {
                    abort(response('Unauthorized', 401));
                    exit;
                } else {
                    return redirect()->back();
                }
            }
        }

        $this->formHandler->setData(collect(request()->all()));

        if ($this->manager->areCommentsEnabledForContext($this->formHandler->getThreadId()) === false) {
            return $this->formFailure(
                $this->formHandler->getSubmissionParameters(),
                [], // TODO: Better error.
                $this->formHandler->blueprintName()
            );
        }

        $commentData = [];

        try {
            $this->formHandler->handleRequest();

            $eventResults = $this->runStatamicCreatingEvent($this->formHandler->getSubmissionData());

            if (array_key_exists(self::KEY_ERRORS, $eventResults)) {
                if (is_array($eventResults[self::KEY_ERRORS]) && count($eventResults[self::KEY_ERRORS]) > 0) {
                    return $this->formFailure(
                        $this->formHandler->getSubmissionParameters(),
                        $eventResults[self::KEY_ERRORS],
                        $this->formHandler->blueprintName()
                    );
                }
            }

            if (array_key_exists(self::KEY_SUBMISSION, $eventResults)) {
                if ($eventResults[self::KEY_SUBMISSION] !== null) {
                    if ($eventResults[self::KEY_SUBMISSION] instanceof MockSubmission) {
                        $commentData = $eventResults[self::KEY_SUBMISSION]->data();
                    }
                }
            }
        } catch (ValidationException $validationException) {
            return $this->formFailure(
                $this->formHandler->getSubmissionParameters(),
                $validationException->errors(),
                $this->formHandler->blueprintName()
            );
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

        if ($this->coreConfig->autoPublishAnonymousPosts === true) {
            $commentData[CommentContract::KEY_PUBLISHED] = true;
        } else {
            if ($this->coreConfig->autoPublishAuthenticatedPosts === true &&
                $this->currentUserIsPublishingComment) {
                $commentData[CommentContract::KEY_PUBLISHED] = true;
            } else {
                $commentData[CommentContract::KEY_PUBLISHED] = false;
            }
        }

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

    private function formFailure($params, $errors, $meerkatBlueprint)
    {
        if (request()->ajax()) {
            return response([
                self::KEY_ERRORS => (new MessageBag($errors))->all(),
            ], 400);
        }

        $redirect = Arr::get($params, MeerkatForm::KEY_PARAM_ERROR_REDIRECT);

        $response = $redirect ? redirect($redirect) : back();

        return $response->withInput()->withErrors(
            $errors,
            MeerkatForm::getFormSessionHandle($meerkatBlueprint)
        );
    }

    /**
     * Runs Statamic's form submission creating event to allow
     * other Statamic addon's to intercept the submission.
     *
     * @param array $data The submission data.
     * @return array
     */
    private function runStatamicCreatingEvent($data)
    {
        $errors = [];
        $mockedSubmission = new MockSubmission();

        $mockedSubmission->data($data);

        FormSubmitted::dispatch($mockedSubmission);

        return [
            self::KEY_ERRORS => $errors,
            self::KEY_SUBMISSION => $mockedSubmission
        ];
    }

    private function formSuccess($params, $data, $meerkatBlueprint)
    {
        if (request()->ajax()) {
            return response([
                self::KEY_SUCCESS => true,
                self::KEY_SUBMISSION => $data
            ]);
        }

        $redirect = Arr::get($params, '_redirect');

        $response = $redirect ? redirect($redirect) : back();

        $mockSubmission = new MockSubmission();
        $mockSubmission->data($data);

        session()->flash(MeerkatForm::getFormSessionHandle($meerkatBlueprint) . '.success', __('Submission successful.'));
        session()->flash(self::KEY_SUBMISSION, $mockSubmission);

        return $response;
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
     * Adds the current authenticated user information, if the email addresses match.
     *
     * @param array $data The comment's data.
     * @return array
     */
    private function fillWithUserData($data)
    {
        $this->currentUserIsPublishingComment = false;

        $currentUser = auth()->user();

        if ($currentUser === null) {
            return $data;
        }

        if (method_exists($currentUser, 'email')) {
            if ($data[AuthorContract::KEY_EMAIL_ADDRESS] === $currentUser->email()) {
                $this->currentUserIsPublishingComment = true;
                $data[AuthorContract::AUTHENTICATED_USER_ID] = $currentUser->getAuthIdentifier();
            }
        }

        return $data;
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

}
