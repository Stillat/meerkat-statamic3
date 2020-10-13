import ActionState from '../../../actionState';
import trans from '../../../../trans';
import Comment from '../../../../Data/Comments/comment';

class EditCommentHandler extends ActionState {

  constructor(comment: Comment) {
    super(comment);

    this.setMessages('edit');
    this.confirmText = trans('actions.edit_confirm_button');
  }

  proceedWith() {
    return this.comment.save();
  }

}

export default EditCommentHandler;
