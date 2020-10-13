import ActionState from '../../../actionState';
import CommentRepository from '../../../../Repositories/commentRepository';

class BulkDeleteHandler extends ActionState {
  constructor() {
    super();

    this.setMessages('bulk_delete');
  }

  proceedWith() {
    return CommentRepository.Instance.deleteMany(this.commentIds);
  }

}

export default BulkDeleteHandler;
