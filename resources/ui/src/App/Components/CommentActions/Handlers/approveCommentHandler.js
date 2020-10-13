import ActionState from '../../../actionState';
import Comment from '../../../../Data/Comments/comment';

class ApproveCommentHandler extends ActionState {

  constructor(comment : Comment) {
    super(comment);

    this.setMessages('approve');
  }

  proceedWith() {
    return this.comment.publish();
  }

}

export default ApproveCommentHandler;
