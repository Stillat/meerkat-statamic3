import ActionState from '../../../actionState';
import CommentRepository from '../../../../Repositories/commentRepository';

class BulkApproveHandler extends ActionState {

  constructor() {
    super();

    this.setMessages('bulk_approve');
  }

  proceedWith() {
    return CommentRepository.Instance.publishMany(this.commentIds);
  }

}

export default BulkApproveHandler;
