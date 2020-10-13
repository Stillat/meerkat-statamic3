import Comment from './comment';
import Type from '../../Types/type';

import {
  applyCollectionSelectable, applyErrorState,
  enforceCollectionType
} from '../Concerns/providesCollectionInteractivity';

class CommentCollection extends Array {

  constructor() {
    super();

    enforceCollectionType(this, Type.typeOf(Comment));
    applyCollectionSelectable(this);
    applyErrorState(this);

    this.anyInView = function (commentIds) {

      for (let i = 0; i < this.length; i += 1) {
        if (commentIds.includes(this[i].id)) {
          return true;
        }
      }

      return false;
    };

    this.whereIn = function (commentIds) {
      let commentsToReturn = [];

      for (let i = 0; i < this.length; i += 1) {
        if (commentIds.includes(this[i].id)) {
          commentsToReturn.push(this[i]);
        }
      }

      return commentsToReturn;
    };

    this.editCount = 0;
    this.replyingCount = 0;

    this.getSelected = function () {
      let selected = [];

      for (let i = 0; i < this.length; i += 1) {
        if (this[i].isSelected === true) {
          selected.push(this[i]);
        }
      }

      return selected;
    }.bind(this);

    this.setEditing = function (comment: Comment) {
      this.editCount += 1;
    }.bind(this);

    this.setReplying = function (comment: Comment) {
      this.replyingCount += 1;
    }.bind(this);

    this.cancelEditing = function (comment: Comment) {
      this.editCount -= 1;
    }.bind(this);

    this.cancelReplying = function (comment: Comment) {
      this.replyingCount -= 1;
    }.bind(this);

    this.cancelAllEditing = function () {
      this.forEach(function (comment: Comment) {
        if (comment.state.isEditing) {
          comment.cancelEditing();
        }
      });

      if (this.editCount < 0) {
        this.editCount = 0;
      }
    }.bind(this);

    this.cancelAllReplying = function () {
      this.forEach(function (comment: Comment) {
        if (comment.state.isReplying) {
          comment.cancelReply();
        }
      });

      if (this.replyingCount < 0) {
        this.replyingCount = 0;
      }
    }.bind(this);

    this.draftAllEditing = function () {
      this.forEach(function (comment: Comment) {
        if (comment.state.isEditing) {
          comment.cancelWithDraft();
        }
      });

      if (this.editCount < 0) {
        this.editCount = 0;
      }
    }.bind(this);

    this.draftAllReplying = function () {
      this.forEach(function (comment: Comment) {
        if (comment.state.isReplying) {
          comment.cancelReplyWithDraft();
        }
      });

      if (this.replyingCount < 0) {
        this.replyingCount = 0;
      }
    }.bind(this);
  }

}

export default CommentCollection;
