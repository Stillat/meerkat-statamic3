import ActionState from '../../../actionState';
import CommentRepository from '../../../../Repositories/commentRepository';

class BulkUnapproveHandler extends ActionState {
  constructor() {
    super();

    this.setMessages('bulk_unapprove');
  }

  proceedWith() {
    return CommentRepository.Instance.unpublishMany(this.commentIds);
  }
}

export default BulkUnapproveHandler;
