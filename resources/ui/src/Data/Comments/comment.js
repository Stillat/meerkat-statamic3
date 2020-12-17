import {Type, Convert, String} from '../../Types/common';
import {canBeSelected} from '../Concerns/canBeSelected';
import Author from './author';
import ThreadContext from './threadContext';
import ErrorResponse from '../../Http/Responses/errorResponse';
import CommentMutationResponse from '../../Http/Responses/commentMutationResponse';
import AffectedCommentsResponse from '../../Http/Responses/AffectedCommentsResponse';

const syncjs = require('syncjs'),
  {DateTime} = require('luxon');

/**
 * @member {boolean} isSelected
 */
class Comment {

  constructor() {

    canBeSelected(this);

    /** {CommentResponse} */
    this._internalCommentResponse = null;

    this.isNew = true;

    this.id = null;
    this.parentId = null;
    this.authorId = null;
    this.parentAuthorId = null;
    this.contextId = null;
    this.internalContextId = null;
    this.internalRoot = null;

    this.ancestors = [];
    this.children = [];
    this.runtimeDate = null;
    this.localDateFormatted = null;
    this.commentDate = null;
    this.commentDateFormatted = null;
    this.replies = [];
    this.content = '';
    this.contentRaw = '';
    this.depth = 1;
    this.descendents = [];
    this.hasReplies = false;
    this.internalContentTruncated = false;
    this.isReply = false;
    this.isParent = false;
    this.isRoot = false;
    this.published = false;
    this.revisionCount = 0;
    this.isDeleted = false;
    this.hasAuthorEmail = false;
    this.hasAuthorName = false;

    this.hasBeenCheckedForSpam = false;
    this.isSpam = null;

    this.state = {
      isEditing: false,
      isReplying: false,
      reply: null
    };

    this.editProperties = null;

    syncjs.reactsToType(this, {'identity': 'id'});
  }

  onCommentPublished() {
    this.published = true;
  }

  onCommentUnpublished() {
    this.published = false;
  }

  onCommentMarkedAsSpam() {
    this.hasBeenCheckedForSpam = true;
    this.isSpam = true;
  }

  onCommentMarkedAsHam() {
    this.hasBeenCheckedForSpam = true;
    this.isSpam = false;
  }

  onCommentRemoved() {
    this.isSelected = false;
    this.cancelReply();
    this.cancelEditing();
    this.isDeleted = true;
  }

  onCommentUpdated(newVal : Comment) {
    this.content = newVal.content;
  }

  startReplying() {
    if (this.state.isEditing === true) {
      this.cancelWithDraft();
    }

    if (this.state.reply === null) {
      this.state.reply = new Comment();
      this.state.reply.parentId = this.id;
    }

    this.state.isReplying = true;

    if (Type.hasValue(this._internalCommentResponse)) {
      this._internalCommentResponse.comments.setReplying(this);
    }
  }

  cancelReplyWithDraft() {
    this.state.isReplying = false;

    if (Type.hasValue(this._internalCommentResponse)) {
      this._internalCommentResponse.comments.cancelReplying(this);
    }
  }

  cancelReply() {
    if (this.state.isReplying === true) {
      this.state.isReplying = false;
      this.state.reply = null;
    }

    if (Type.hasValue(this._internalCommentResponse)) {
      this._internalCommentResponse.comments.cancelReplying(this);
    }
  }

  startEditing() {
    if (this.editProperties === null) {
      this.editProperties = Object.assign({}, this);
    }

    this.state.isEditing = true;

    if (Type.hasValue(this._internalCommentResponse)) {
      this._internalCommentResponse.comments.setEditing(this);
    }
  }

  cancelWithDraft() {
    this.state.isEditing = false;

    if (Type.hasValue(this._internalCommentResponse)) {
      this._internalCommentResponse.comments.cancelEditing(this);
    }
  }

  cancelEditing() {
    this.state.isEditing = false;
    this.editProperties = null;

    if (Type.hasValue(this._internalCommentResponse)) {
      this._internalCommentResponse.comments.cancelEditing(this);
    }
  }

  /**
   * Tests if the comment has an author.
   *
   * @returns {boolean}
   */
  hasAuthor(): Boolean {
    return String.hasValue(this.authorId);
  }

  /**
   * Tests if the comment has a parent author.
   *
   * @returns {boolean}
   */
  hasParentAuthor(): Boolean {
    return String.hasValue(this.parentAuthorId);
  }

  /**
   * Tests if the comment has a thread context available.
   *
   * @returns {boolean}
   */
  hasThreadContext(): Boolean {
    return String.hasValue(this.contextId);
  }

  /**
   * Attempts to retrieve the comment's author.
   *
   * @returns {null|Author}
   */
  getAuthor(): Author {
    if (!Type.hasValue(this._internalCommentResponse)) {
      return null;
    }

    if (this.hasAuthor()) {
      return this._internalCommentResponse.getResponseAuthor(this.authorId);
    }

    return null;
  }

  /**
   * Attempts to retrieve the comment's parent author, if available.
   *
   * @returns {null|Author}
   */
  getParentAuthor(): Author {
    if (!Type.hasValue(this._internalCommentResponse)) {
      return null;
    }

    if (this.hasParentAuthor()) {
      return this._internalCommentResponse.getResponseAuthor(this.parentAuthorId);
    }

    return null;
  }

  /**
   * Attempts to retrieve the comment's thread context, if available.
   *
   * @returns {ThreadContext|null}
   */
  getThreadContext(): ThreadContext {
    if (!Type.hasValue(this._internalCommentResponse)) {
      return null;
    }

    if (this.hasThreadContext()) {
      return this._internalCommentResponse.getResponseThread(this.contextId);
    }

    return null;
  }

  /**
   * Gets the thread name, if available.
   *
   * @returns {string}
   */
  getThreadName() : String {
    let threadContext = this.getThreadContext();

    if (threadContext !== null) {
      return threadContext.name;
    }

    return '';
  }

  /**
   * Converts an API response object to a new Comment instance.
   *
   * @param {Object} apiObject The API response object.
   * @returns {Comment}
   */
  static fromApiObject(apiObject: Object): Comment {
    let comment = new Comment();

    comment.id = Type.withDefault(apiObject[Comment.ApiId], null);

    if (comment.id === null) {
      throw new Error('Comment with a NULL id was supplied.');
    }

    comment.authorId = Type.withDefault(apiObject[Comment.ApiAuthor], null);
    comment.parentAuthorId = Type.withDefault(apiObject[Comment.ApiParentAuthor], null);
    comment.contextId = Type.withDefault(apiObject[Comment.ApiContext], null);
    comment.internalContextId = Type.withDefault(apiObject[Comment.ApiInternalContextId], null);
    comment.internalRoot = Type.withDefault(apiObject[Comment.ApiInternalRoot], Convert.toInt(comment.id));
    comment.ancestors = Type.withDefault(apiObject[Comment.ApiAncestors], []);
    comment.children = Type.withDefault(apiObject[Comment.ApiChildren], []);
    comment.commentDate = Type.withDefault(apiObject[Comment.ApiCommentDate], null);
    comment.commentDateFormatted = Type.withDefault(apiObject[Comment.ApiCommentDateFormatted], null);
    comment.replies = Type.withDefault(apiObject[Comment.ApiComments], []);
    comment.content = Type.withDefault(apiObject[Comment.ApiContent], '');
    comment.contentRaw = Type.withDefault(apiObject[Comment.ApiContentRaw], '');
    comment.runtimeDate = DateTime.fromSeconds(parseInt(comment.id, 10), {zone: 'UTC'}).toLocal();
    comment.localDateFormatted = comment.runtimeDate.toLocaleString(DateTime.DATETIME_MED);
    comment.depth = Type.withDefault(apiObject[Comment.ApiDepth], 1);
    comment.descendents = Type.withDefault(apiObject[Comment.ApiDescendents], []);
    comment.hasReplies = Type.withDefault(apiObject[Comment.ApiHasReplies], false);
    comment.internalContentTruncated = Type.withDefault(apiObject[Comment.ApiInternalContentTruncated], false);
    comment.isReply = Type.withDefault(apiObject[Comment.ApiIsReply], false);
    comment.isParent = Type.withDefault(apiObject[Comment.ApiIsParent], false);
    comment.parentId = Type.withDefault(apiObject[Comment.ApiParentId], null);
    comment.isRoot = Type.withDefault(apiObject[Comment.ApiIsRoot], true);
    comment.published = Type.withDefault(apiObject[Comment.ApiPublished], false);
    comment.revisionCount = Type.withDefault(apiObject[Comment.ApiRevisionCount], 0);
    comment.hasBeenCheckedForSpam = Type.withDefault(apiObject[Comment.ApiHasCheckedForSpam], false);
    comment.hasAuthorEmail = Type.withDefault(apiObject[Comment.ApiCommentHasAuthorEmail], false);
    comment.hasAuthorName = Type.withDefault(apiObject[Comment.ApiCommentHasAuthorName], false);

    if (comment.hasBeenCheckedForSpam) {
      comment.isSpam = Type.withDefault(apiObject[Comment.ApiSpam], true);
    } else {
      comment.isSpam = null;
    }

    return comment;
  }

  saveReply(): Promise<CommentMutationResponse | ErrorResponse> {
    if (this.state.isReplying === true && this.state.reply !== null) {
      let contentToSave = this.state.reply.content;

      return new Promise(function (resolve, reject) {
        this._internalCommentResponse._originator.attachReply(this.id, contentToSave).then(function (result) {
          if (result.success) {
            this.descendents.push(result.comment.id);
            this.replies.push(result.comment.id);
            this.cancelReply();
          }

          resolve(result);
        }.bind(this)).catch(function (err) {
          reject(err);
        });
      }.bind(this));
    }

    return new Promise(function (resolve, reject) {
      reject(ErrorResponse.makeStateError());
    });
  }

  save(): Promise<CommentMutationResponse | ErrorResponse> {
    let contentToSave = this.content;

    if (this.state.isEditing && this.editProperties !== null) {
      contentToSave = this.editProperties.content;
    }

    return new Promise(function (resolve, reject) {
      this._internalCommentResponse._originator.update(this.id, contentToSave).then(function (result) {

        if (result.success) {
          this.content = result.comment.content;

          if (this.state.isEditing) {
            this.cancelEditing();
          }
        }

        resolve(result);
      }.bind(this)).catch(function (err) {
        reject(err);
      });
    }.bind(this));
  }

  delete(): Promise<AffectedCommentsResponse | ErrorResponse> {
    return new Promise(function (resolve, reject) {
      this._internalCommentResponse._originator.delete(this.id).then(function (result) {
        if (result.success) {
          this.isDeleted = true;
        }

        resolve(result);
      }.bind(this)).catch(function (err) {
        reject(err);
      });
    }.bind(this));
  }

  publish(): Promise<CommentMutationResponse | ErrorResponse> {
    return new Promise(function (resolve, reject) {
      this._internalCommentResponse._originator.publish(this.id).then(function (result) {
        if (result.success) {
          this.published = true;
        }

        resolve(result);
      }.bind(this)).catch(function (err) {
        reject(err);
      });
    }.bind(this));
  }

  unpublish(): Promise<CommentMutationResponse | ErrorResponse> {
    return new Promise(function (resolve, reject) {
      this._internalCommentResponse._originator.unpublish(this.id).then(function (result) {
        if (result.success) {
          this.published = false;
        }

        resolve(result);
      }.bind(this)).catch(function (err) {
        reject(err);
      });
    }.bind(this));
  }

  markAsSpam(): Promise<CommentMutationResponse | ErrorResponse> {
    return new Promise(function (resolve, reject) {
      this._internalCommentResponse._originator.markAsSpam(this.id).then(function (result) {
        if (result.success) {
          this.hasBeenCheckedForSpam = true;
          this.isSpam = true;
        }

        resolve(result);
      }.bind(this)).catch(function (err) {
        reject(err);
      });
    }.bind(this));
  }

  markAsNotSpam(): Promise<CommentMutationResponse | ErrorResponse> {
    return new Promise(function (resolve, reject) {
      this._internalCommentResponse._originator.markAsNotSpam(this.id).then(function (result) {
        if (result.success) {
          this.hasBeenCheckedForSpam = true;
          this.isSpam = false;
        }

        resolve(result);
      }.bind(this)).catch(function (err) {
        reject(err);
      });
    }.bind(this));
  }

}

Comment.ApiAncestors = 'ancestors';
Comment.ApiAuthor = 'author';
Comment.ApiParentAuthor = 'internal_parent_author';
Comment.ApiChildren = 'children';
Comment.ApiCommentDate = 'comment_date';
Comment.ApiCommentDateFormatted = 'comment_date_formatted';
Comment.ApiComments = 'comments';
Comment.ApiContent = 'content';
Comment.ApiContentRaw = 'content_raw';
Comment.ApiContext = 'context';
Comment.ApiDepth = 'depth';
Comment.ApiDescendents = 'descendents';
Comment.ApiHasReplies = 'has_replies';
Comment.ApiId = 'id';
Comment.ApiCommentHasAuthorEmail = 'internal_author_has_email';
Comment.ApiCommentHasAuthorName = 'internal_author_has_name';
Comment.ApiInternalContentTruncated = 'internal_content_truncated';
Comment.ApiInternalContextId = 'internal_context_id';
Comment.ApiInternalRoot = 'internal_root';
Comment.ApiIsReply = 'isReply';
Comment.ApiParentId = 'parent';
Comment.ApiIsParent = 'is_parent';
Comment.ApiIsRoot = 'is_root';
Comment.ApiPublished = 'published';
Comment.ApiRevisionCount = 'revision_count';
Comment.ApiSpam = 'spam';
Comment.ApiHasCheckedForSpam = 'has_checked_for_spam';

export default Comment;
