import template from './template.html';
import ActionState from '../../actionState';
import String from '../../../Types/string';
import Guid from '../../../Types/guid';
import Environment from '../../../Config/environment';
import UsesTranslator from '../../Mixins/usesTranslator';

require('./style.less');

export default {
  template: template,
  mixins: [UsesTranslator],
  props: {
    actionState: {
      type: ActionState,
      default: null
    }
  },
  watch: {
    'actionState.isErrorState': function (val) {
      if (val === true) {
        this.jiggle();
      }
    }
  },
  data() {
    return {
      modalId: 'meerkat__modal-' + Guid.newGuid()
    };
  },
  methods: {
    jiggle() {
      let nonPortalModalParent = Environment.$('#' + this.modalId).parent();

      nonPortalModalParent.addClass('meerkat__modal--error-state').delay(820).queue(function (n) {
        nonPortalModalParent.removeClass('meerkat__modal--error-state');
        n();
      });
    },
    getInteractionProperties() {
      return {
        state: this.actionState,
        comment: this.actionState.comment
      };
    },
    dismiss() {
      this.$emit('cancel');
    },
    submit() {
      this.$emit('confirm');
    }
  },
  computed: {
    progressColor() {
      if (this.actionState.isProcessTakingTooLong) {
        return '#f1c40f';
      }

      if (this.actionState.promptAbandon) {
        return '#e74c3c';
      }

      return '#3498db';
    },
    errorMessage() {
      if (this.actionState.hasResponse === true && String.hasValue(this.actionState.response.msg)) {
        return this.actionState.response.msg;
      }

      return this.actionState.errorMessage;
    },
    progressMessage() {
      if (this.actionState.isProcessTakingTooLong) {
        return this.actionState.tooLongMessage;
      }

      return this.actionState.progressMessage;
    },
    titleMessage() {
      if (this.actionState.isProcessing && String.hasValue(this.actionState.activeTitle)) {
        return this.actionState.activeTitle;
      }

      return this.actionState.title;
    },
    buttonClass() {
      return this.actionState.isDestructive ? 'btn-danger' : 'btn-primary';
    }
  },
  created() {
    this.$keys.bind('esc', this.dismiss);
    this.$keys.bind('enter', this.submit);
  }
};
