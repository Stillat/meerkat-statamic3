import template from './template.html';
import UsesTranslator from '../../Mixins/usesTranslator';
import ActionHandler from '../../Mixins/actionHandler';
import CanDismiss from '../../Mixins/canDismissAction';
import CanPerformAction from '../../Mixins/canPerformAction';
import {
  ApproveCommentHandler, DeleteCommentHandler,
  MarkAsNotSpamHandler, MarkAsSpamHandler, UnApproveCommentHandler
} from './Handlers';

require('./style.less');

export default {
  mixins: [UsesTranslator, ActionHandler, CanDismiss, CanPerformAction],
  template: template,
  props: {
    comment: {
      type: Object,
      default: null
    },
    permissions: {
      type: Object,
      default: null,
      required: true
    }
  },
  data() {
    return {
      currentAction: null,
      handlers: {
        'approve': ApproveCommentHandler,
        'unapprove': UnApproveCommentHandler,
        'delete': DeleteCommentHandler,
        'mark-spam': MarkAsSpamHandler,
        'mark-ham': MarkAsNotSpamHandler
      }
    };
  }
};
