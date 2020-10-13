import NavigationBadge from '../App/Components/NavigationBadge';
import CommentEditor from '../App/Components/CommentEditor';
import StatefulConfirmationDialog from '../App/Components/StatefulConfirmationDialog';
import Loader from '../App/Components/Loader';
import CommentThread from '../App/CommentThread';
import Environment from '../Config/environment';

import Type from '../Types/type';

export function registerVueComponents(vue) {
  let registerCallback = vue.component;

  if (Type.hasValue(Environment.ContextComponentRegister)) {
    registerCallback = Environment.ContextComponentRegister;
  }

  registerCallback('meerkat-nav-badge', NavigationBadge);
  registerCallback('meerkat-comment-editor', CommentEditor);
  registerCallback('meerkat-comment-thread', CommentThread);
  registerCallback('meerkat-loader', Loader);
  registerCallback('meerkat-stateful-confirm-dialog', StatefulConfirmationDialog);

}
