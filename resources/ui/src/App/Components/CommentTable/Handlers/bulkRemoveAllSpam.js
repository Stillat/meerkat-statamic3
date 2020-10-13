import ActionState from '../../../actionState';
import CommentRepository from '../../../../Repositories/commentRepository';

class BulkRemoveAllSpam extends ActionState {
  constructor() {
    super();

    this.setMessages('bulk_remove_spam');
  }

  proceedWith() {
    return CommentRepository.Instance.removeAllSpam();
  }
}

export default BulkRemoveAllSpam;
