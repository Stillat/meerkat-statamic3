import template from './template.html';
import UsesTranslator from '../../Mixins/usesTranslator';
import ActionHandler from '../../Mixins/actionHandler';
import CanDismissAction from '../../Mixins/canDismissAction';
import LostChangesHandler from '../CommentEditor/Handlers/lostChangesHandler';
import ActionState from '../../actionState';

require('./style.less');

export default {
  template: template,
  mixins: [UsesTranslator, ActionHandler, CanDismissAction],
  props: {
    comment: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      currentAction: null
    };
  },
  methods: {
    cancel() {
      if (this.comment.state.reply.content.trim().length > 0) {
        let handler = new LostChangesHandler(this.comment);

        this.confirm(handler).onConfirm(function (state : ActionState) {
          this.$emit('reply-canceled', this.comment);
          this.comment.cancelReply();
        }.bind(this));

        return;
      }

      this.$emit('reply-canceled', this.comment);
      this.comment.cancelReply();
    }
  },
  mounted() {
    this.$refs.markdownEditor.focus();
  },
  created() {
    this.$keys.bind('esc', this.cancel);
  }
};
