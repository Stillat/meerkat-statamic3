import ActionState from '../../../actionState';
import CommentRepository from '../../../../Repositories/commentRepository';

class BulkSpamHandler extends ActionState {
  constructor() {
    super();

    this.setMessages('bulk_mark_spam');
  }

  proceedWith() {
    return CommentRepository.Instance.markManyAsSpam(this.commentIds);
  }
}

export default BulkSpamHandler;
