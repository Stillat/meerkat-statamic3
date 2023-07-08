import EventEmitter from 'wolfy87-eventemitter';
import trans from '../trans';
import ControlPanelApplication from './controlPanelApplication';
import Type from '../Types/type';
import ErrorResponse from '../Http/Responses/errorResponse';
import Guid from '../Types/guid';
import Comment from '../Data/Comments/comment';

class ActionState extends EventEmitter {
  constructor(comment : Comment) {
    super();

    ActionState.CurrentActionId = Guid.newGuid();

    this.comment = comment;
    this.commentIds = [];
    this.display = false;
    this.title = 'Please override this title: title';
    this.activeTitle = '';
    this.message = 'Please override this message: message';
    this.successMessage = 'Please override this message: successMessage';

    this.progressMessage = 'Please override this message: progressMessage';
    this.tooLongMessage = trans('errors.process_taking_too_long');

    this.errorMessage = trans('errors.general');
    this.abandonMessage = trans('errors.process_abandon');
    this.abandonedMessage = 'Please override this message: abandonedMessage';
    this.clientErrorMessage = trans('errors.client_side_error');
    this.repeatedFailureMessage = trans('errors.process_repeated_failure');
    this.unauthorizedMessage = trans('errors.permissions');

    this.tryAgain = trans('actions.try_again');
    this.cancelText = trans('actions.cancel');
    this.confirmText = trans('actions.confirm');

    this.hasResponse = false;
    this.response = null;

    this.lastClientError = null;

    this.hasInteraction = false;
    this.interactionComponent = '';
    this.numberOfErrorsEncountered = 0;
    this.isDestructive = false;
    this.isProcessing = false;
    this.doesNotHavePrivileges = false;
    this.isErrorState = false;
    this.wasClientError = false;
    this.promptAbandon = false;
    this.isProcessTakingTooLong = false;
    this.longProcessTrigger = 10000;
    this.giveUpPromptTrigger = 3000;
    this.failedRequestCutoff = 2;
    this.isLoadingLog = false;
    this.serverErrorReport = null;
    this.isSendingErrorLog = null;
    this.missingPermission = null;
    this.timeouts = [];
  }

  /**
   * Updates all messages with the provided action scope.
   *
   * @param {string} messageScope The action translation prefix.
   */
  setMessages(messageScope) {
    this.title = trans('actions.' + messageScope + '_confirm_title');
    this.activeTitle = trans('actions.' + messageScope + '_confirm_title_active');
    this.message = trans('actions.' + messageScope + '_confirm_message');
    this.progressMessage = trans('actions.' + messageScope + '_confirm_progress_message');
    this.abandonedMessage = trans('actions.' + messageScope + '_confirm_abandoned');
    this.errorMessage = trans('actions.' + messageScope + '_error_encountered');
    this.successMessage = trans('actions.' + messageScope + '_success');
  }

  _clearErrorState() {
    this.isLoadingLog = false;
    this.serverErrorReport = null;
    this.isSendingErrorLog = null;
    this.isErrorState = false;
    this.wasClientError = false;
    this.promptAbandon = false;
    this.numberOfErrorsEncountered = 0;
    this.missingPermission = null;
    this.doesNotHavePrivileges = false;
  }

  canDismiss() {
    return !this.isProcessing;
  }

  _abandon() {
    this.emit(ActionState.EventAbandoned, this);
    ControlPanelApplication.controlPanel().message().info(this.abandonedMessage);

    this._cancel();
  }

  _cancel() {
    this._clearErrorState();
    this.resetProcessingState();
    this.display = false;
    this.emit(ActionState.EventCanceled, this);
  }

  dismiss() {
    this._cancel();
  }

  _tryAgain() {
    this.resetProcessingState();
    this.proceed();
  }

  _submitAndTryAgain() {
    this._clearErrorState();
    this._tryAgain();
  }

  _confirm() {
    this.emit(ActionState.EventConfirmed, this);
  }

  _clearWatchers() {
    for (let i = 0; i < this.timeouts.length; i += 1) {
      clearTimeout(this.timeouts[i]);
    }
  }

  _notAuthorized(permission) {
    this._clearErrorState();
    this.resetProcessingState();
    this.doesNotHavePrivileges = true;
    this.missingPermission = Type.withDefault(permission, null);
  }

  _complete() {
    this.resetProcessingState();
    this.display = false;
    ControlPanelApplication.controlPanel().message().success(this.successMessage);
    this.emit(ActionState.EventComplete, this);
  }

  _startGiveUpTimer() {
    let intervalId = setTimeout(function () {
      this.promptAbandon = true;
      this.isProcessTakingTooLong = false;
    }.bind(this), this.giveUpPromptTrigger);

    this.timeouts.push(intervalId);
  }

  _startWatchingProgress() {
    let intervalId = setTimeout(function () {
      this.isProcessTakingTooLong = true;
      this._startGiveUpTimer();
    }.bind(this), this.longProcessTrigger);

    this.timeouts.push(intervalId);
  }

  resetProcessingState() {
    this._clearWatchers();
    this.isLoadingLog = false;
    this.isProcessing = false;
    this.isProcessTakingTooLong = false;
    this.promptAbandon = false;
  }

  errorEncountered(incrementErrorCounter: Boolean) {
    incrementErrorCounter = Type.withDefault(incrementErrorCounter, true);

    this.isErrorState = true;
    this.resetProcessingState();

    if (incrementErrorCounter) {
      this.numberOfErrorsEncountered += 1;
    }
  }

  proceed(donePromise: Promise) {
    this.isProcessing = true;
    this.isErrorState = false;
    this._startWatchingProgress();

    if (Type.hasValue(this[ActionState.HandleProceedWith])) {
      this.proceedWith()
        .then(function (result) {
          if (result.success) {
            this._complete();
          } else {
            this.wasClientError = false;
            this.response = result;
            this.errorEncountered(!result.isRecoverable);
            this.hasResponse = true;
          }
        }.bind(this))
        .catch(function (err) {
          if (Type.isTypeOf(err, ErrorResponse)) {
            if (err.authorized === false) {
              this._notAuthorized(err.permission);
            } else {
              this.wasClientError = true;
              this.lastClientError = err;
              this.errorEncountered();
            }
          } else {
            this.errorEncountered();
          }
        }.bind(this));
    }
  }

  start() {
    this._confirm();

    return this;
  }

  onConfirm(callback): ActionState {
    this.on(ActionState.EventConfirmed, callback);

    return this;
  }

  onCancel(callback): ActionState {
    this.on(ActionState.EventCanceled, callback);

    return this;
  }

  onAbandoned(callback): ActionState {
    this.on(ActionState.EventAbandoned, callback);

    return this;
  }

  onComplete(callback): ActionState {
    this.on(ActionState.EventComplete, callback);

    return this;
  }

  onUnauthorized(callback): ActionState {
    this.on(ActionState.EventUnauthorized, callback);

    return this;
  }

}

ActionState.CurrentActionId = null;
ActionState.HandleProceedWith = 'proceedWith';
ActionState.EventCanceled = 'canceled';
ActionState.EventAbandoned = 'abandoned';
ActionState.EventConfirmed = 'confirmed';
ActionState.EventComplete = 'complete';
ActionState.EventUnauthorized = 'unauthorized';

export default ActionState;
