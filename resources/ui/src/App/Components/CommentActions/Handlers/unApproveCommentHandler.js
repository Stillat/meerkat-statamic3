import ActionState from '../../../actionState';
import Comment from '../../../../Data/Comments/comment';

class UnApproveCommentHandler extends ActionState {

  constructor(comment : Comment) {
    super(comment);

    this.setMessages('unapprove');
  }

  proceedWith() {
    return this.comment.unpublish();
  }

}

export default UnApproveCommentHandler;
