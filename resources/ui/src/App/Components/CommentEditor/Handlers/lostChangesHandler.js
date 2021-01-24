import ActionState from '../../../actionState';
import Comment from '../../../../Data/Comments/comment';
import trans from '../../../../trans';

class LostChangesHandler extends ActionState {

  constructor(comment : Comment) {
    super(comment);

    this.setMessages('edit_unsaved_changes');
    this.confirmText = trans('actions.discard_changes');
  }

}

export default LostChangesHandler;
