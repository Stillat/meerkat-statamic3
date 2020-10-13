import ActionState from '../../../actionState';
import trans from '../../../../trans';
import Comment from '../../../../Data/Comments/comment';

class ReplyCommentHandler extends ActionState {

  constructor(comment: Comment) {
    super(comment);

    this.setMessages('reply');
    this.confirmText = trans('actions.reply_confirm_button');
  }

  proceedWith() {
    return this.comment.saveReply();
  }

}

export default ReplyCommentHandler;
