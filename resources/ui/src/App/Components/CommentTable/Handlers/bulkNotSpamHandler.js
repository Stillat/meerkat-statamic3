import ActionState from '../../../actionState';
import CommentRepository from '../../../../Repositories/commentRepository';

class BulkNotSpamHandler extends ActionState {
  constructor() {
    super();

    this.setMessages('bulk_mark_ham');
  }

  proceedWith() {
    return CommentRepository.Instance.markManyAsNotSpam(this.commentIds);
  }
}

export default BulkNotSpamHandler;
