import template from './template.html';
import UsesTranslator from '../../Mixins/usesTranslator';
import ActionHandler from '../../Mixins/actionHandler';
import {
  ApproveCommentHandler, DeleteCommentHandler,
  MarkAsNotSpamHandler, MarkAsSpamHandler, UnApproveCommentHandler
} from './Handlers';
import ActionState from '../../actionState';
import Type from '../../../Types/type';

require('./style.less');

export default {
  mixins: [UsesTranslator, ActionHandler],
  template: template,
  props: {
    comment: {
      type: Comment,
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
  },
  methods: {
    forceDismiss() {
      if (this.currentAction !== null) {
        this.currentAction.dismiss();
      }
    },
    checkForDismiss() {
      if (this.currentAction !== null && this.currentAction.display === true && this.currentAction.canDismiss()) {
        this.currentAction.dismiss();
      }
    },
    performAction(action, comment) {
      if (Type.hasValue(this.handlers[action])) {
        this.confirm(new this.handlers[action](comment))
          .onConfirm((state: ActionState) => {
            state.proceed();
          });
      }
    }
  }
};
