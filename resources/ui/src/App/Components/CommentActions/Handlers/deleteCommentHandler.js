import ActionState from '../../../actionState';
import Comment from '../../../../Data/Comments/comment';

class DeleteCommentHandler extends ActionState {

  constructor(comment : Comment) {
    super(comment);

    this.setMessages('delete');
  }

  proceedWith() {
    return this.comment.delete();
  }

}

export default DeleteCommentHandler;
