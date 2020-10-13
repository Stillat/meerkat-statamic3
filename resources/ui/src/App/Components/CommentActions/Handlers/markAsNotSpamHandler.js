import ActionState from '../../../actionState';
import Comment from '../../../../Data/Comments/comment';

class MarkAsNotSpamHandler extends ActionState {

  constructor(comment : Comment) {
    super(comment);

    this.setMessages('mark_ham');
  }

  proceedWith() {
    return this.comment.markAsNotSpam();
  }

}

export default MarkAsNotSpamHandler;
