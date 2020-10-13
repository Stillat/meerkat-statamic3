import Endpoints from '../Http/endpoints';
import Client from '../Http/client';
import CommentResponse from '../Http/Responses/commentResponse';
import ErrorResponse from '../Http/Responses/errorResponse';
import SearchOptions from '../Data/Comments/searchOptions';
import {canPoolHttpRequests} from '../Data/Concerns/canPoolHttpRequests';
import CommentMutationResponse from '../Http/Responses/commentMutationResponse';
import AffectedCommentsResponse from '../Http/Responses/AffectedCommentsResponse';
import {hash} from '../Data/Concerns/canBeStringOrHash';
import ActionState from '../App/actionState';
import TaskResponse from '../Http/Responses/taskResponse';

const syncjs = require('syncjs');

/**
 * Provides a wrapper around Meerkat's comment-related HTTP API endpoints.
 *
 * @property {function(request, waitTime) : RequestState} shouldProcessRequest()
 * @property {function(request)} releasePending()
 */
class CommentRepository {

  constructor() {
    canPoolHttpRequests(this);
    this.client = new Client();
  }

  /**
   * Issues a comment search request.
   *
   * @param {SearchOptions} options The search options.
   * @returns {Promise<CommentResponse | ErrorResponse>}
   */
  search(options: SearchOptions): Promise<CommentResponse | ErrorResponse> {
    let requestHash = options.toHash();

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentsSearch), options.getRequestData(), requestState)
        .then(function (result) {
          this.releasePending(requestHash);
          resolve(CommentResponse.fromApiResponse(result, this));
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Attempts to attach a reply to provided comment identifier.
   *
   * @param {string} replyingToId The parent identifier.
   * @param {string} newCommentContent The content of the new comment.
   * @returns {Promise<CommentMutationResponse | ErrorResponse>}
   */
  attachReply(replyingToId: string, newCommentContent: string): Promise<CommentMutationResponse | ErrorResponse> {
    let request = {
        replyingTo: replyingToId,
        comment: newCommentContent,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentsReply), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);
          resolve(CommentMutationResponse.fromApiResponse(result));
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Attempts to publish the requested comment.
   *
   * @param {string} commentId The comment identifier.
   * @returns {Promise<CommentMutationResponse | ErrorResponse>}
   */
  publish(commentId: string): Promise<CommentMutationResponse | ErrorResponse> {
    let request = {
        comment: commentId,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentsApprove), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);

          let publishResult = CommentMutationResponse.fromApiResponse(result);

          if (publishResult.success) {
            syncjs.Hubs.comments().published([publishResult.comment.id]);
          }

          resolve(publishResult);
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Attempts to publish the provided comments.
   *
   * @param {Array<string>} commentIds The comment identifiers.
   * @returns {Promise<AffectedCommentsResponse | ErrorResponse>}
   */
  publishMany(commentIds: Array<string>): Promise<AffectedCommentsResponse | ErrorResponse> {
    let request = {
        comments: commentIds,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentsApproveMany), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);

          let publishResult = AffectedCommentsResponse.fromApiResponse(result);

          if (publishResult.partialSuccess || publishResult.success) {
            syncjs.Hubs.comments().published(publishResult.comments);
          }

          resolve(publishResult);
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Attempts to unpublish the requested comment.
   *
   * @param {string} commentId The comment identifier.
   * @returns {Promise<CommentMutationResponse | ErrorResponse>}
   */
  unpublish(commentId: string): Promise<CommentMutationResponse | ErrorResponse> {
    let request = {
        comment: commentId,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentsUnapprove), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);

          let unpublishedResult = CommentMutationResponse.fromApiResponse(result);

          if (unpublishedResult.success) {
            syncjs.Hubs.comments().unpublished([unpublishedResult.comment.id]);
          }

          resolve(unpublishedResult);
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Attempts to remove the provided comment.
   *
   * @param {string} commentId The comment identifier.
   * @returns {Promise<AffectedCommentsResponse | ErrorResponse>}
   */
  delete(commentId: string): Promise<AffectedCommentsResponse | ErrorResponse> {
    let request = {
        comment: commentId,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentsRemove), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);

          let deleteResponse = AffectedCommentsResponse.fromApiResponse(result);

          if (deleteResponse.success) {
            syncjs.Hubs.comments().removed(deleteResponse.comments);
          }

          resolve(deleteResponse);
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Attempts to remove the requested comments.
   *
   * @param {Array<string>} commentIds The comment identifiers.
   * @returns {Promise<AffectedCommentsResponse | ErrorResponse>}
   */
  deleteMany(commentIds: Array<string>): Promise<AffectedCommentsResponse | ErrorResponse> {
    let request = {
        comments: commentIds,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentsRemoveMany), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);

          let deleteResponse = AffectedCommentsResponse.fromApiResponse(result);

          if (deleteResponse.partialSuccess || deleteResponse.success) {
            syncjs.Hubs.comments().removed(deleteResponse.comments);
          }

          resolve(deleteResponse);
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Attempts to remove all comments currently marked as spam.
   *
   * @returns {Promise<AffectedCommentsResponse | ErrorResponse>}
   */
  removeAllSpam(): Promise<AffectedCommentsResponse | ErrorResponse> {
    let request = {
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentsRemoveSpam), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);

          let deleteResponse = AffectedCommentsResponse.fromApiResponse(result);

          if (deleteResponse.partialSuccess || deleteResponse.success) {
            syncjs.Hubs.comments().removed(deleteResponse.comments);
          }

          resolve(deleteResponse);
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Attempts to unpublish the provided comments.
   *
   * @param {Array<string>} commentIds The comment identifiers.
   * @returns {Promise<AffectedCommentsResponse | ErrorResponse>}
   */
  unpublishMany(commentIds: Array<string>): Promise<AffectedCommentsResponse | ErrorResponse> {
    let request = {
        comments: commentIds,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentsUnapproveMany), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);

          let unpublishResult = AffectedCommentsResponse.fromApiResponse(result);

          if (unpublishResult.partialSuccess || unpublishResult.success) {
            syncjs.Hubs.comments().unpublished(unpublishResult.comments);
          }

          resolve(unpublishResult);
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Attempts to update the comment's content.
   *
   * @param {string} commentId The comment identifier.
   * @param {string} newContent The new comment content.
   * @returns {Promise<CommentMutationResponse | ErrorResponse>}
   */
  update(commentId: string, newContent: string): Promise<CommentMutationResponse | ErrorResponse> {
    let request = {
        comment: commentId,
        content: newContent,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentsUpdate), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);

          let updateResult = CommentMutationResponse.fromApiResponse(result);

          if (updateResult.success) {
            syncjs.Hubs.comments().updated([result.comment]);
          }

          resolve(updateResult);
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Attempts to mark the comment as spam.
   *
   * @param {string} commentId The comment identifier.
   * @returns {Promise<CommentMutationResponse | ErrorResponse>}
   */
  markAsSpam(commentId: string): Promise<CommentMutationResponse | ErrorResponse> {
    let request = {
        comment: commentId,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentMarkSpam), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);

          let spamResult = CommentMutationResponse.fromApiResponse(result);

          if (spamResult.success && spamResult.autoDeleted === false) {
            syncjs.Hubs.comments().markedAsSpam([spamResult.comment.id]);
          } else if (spamResult.success && spamResult.autoDeleted === true) {
            syncjs.Hubs.comments().removed(spamResult.comments);
          }

          resolve(spamResult);
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Attempts to mark the provided comments as spam.
   *
   * @param {Array<string>} commentIds The comment identifiers.
   * @returns {Promise<AffectedCommentsResponse | ErrorResponse>}
   */
  markManyAsSpam(commentIds: Array<string>): Promise<AffectedCommentsResponse | ErrorResponse> {
    let request = {
        comments: commentIds,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentMarkManySpam), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);

          let markSpamResult = AffectedCommentsResponse.fromApiResponse(result);

          if (markSpamResult.partialSuccess || markSpamResult.success) {
            syncjs.Hubs.comments().markedAsSpam(markSpamResult.comments);
          }

          resolve(markSpamResult);
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Attempts to mark the comment as not spam.
   *
   * @param {string} commentId The comment identifier.
   * @returns {Promise<CommentMutationResponse | ErrorResponse>}
   */
  markAsNotSpam(commentId: string): Promise<CommentMutationResponse | ErrorResponse> {
    let request = {
        comment: commentId,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentMarkHam), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);

          let markHamResult = CommentMutationResponse.fromApiResponse(result);

          if (markHamResult.success) {
            syncjs.Hubs.comments().markedAsHam([markHamResult.comment.id]);
          }

          resolve(markHamResult);
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Issues a request to check all pending comments for spam.
   *
   * @returns {Promise<TaskResponse | ErrorResponse>}
   */
  checkForSpam(): Promise<TaskResponse | ErrorResponse> {
    let request = {'checkForSpam': true}, requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentsCheckForSpam), {}, requestState)
        .then(function (result) {
          this.releasePending(requestHash);

          resolve(TaskResponse.fromApiResponse(result));
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  /**
   * Attempts to mark the provided comments as not spam.
   *
   * @param {Array<string>} commentIds The comment identifiers.
   * @returns {Promise<AffectedCommentsResponse | ErrorResponse>}
   */
  markManyAsNotSpam(commentIds: Array<string>): Promise<AffectedCommentsResponse | ErrorResponse> {
    let request = {
        comments: commentIds,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.CommentMarkManyHam), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);

          let markHamResult = AffectedCommentsResponse.fromApiResponse(result);

          if (markHamResult.partialSuccess || markHamResult.success) {
            syncjs.Hubs.comments().markedAsHam(markHamResult.comments);
          }

          resolve(markHamResult);
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

}

CommentRepository.Instance = new CommentRepository();

export default CommentRepository;
