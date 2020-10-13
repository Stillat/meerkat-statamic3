import ActionState from '../../../actionState';
import Comment from '../../../../Data/Comments/comment';

class MarkAsSpamHandler extends ActionState {

  constructor(comment : Comment) {
    super(comment);

    this.setMessages('mark_spam');
  }

  proceedWith() {
    return this.comment.markAsSpam();
  }

}

export default MarkAsSpamHandler;
