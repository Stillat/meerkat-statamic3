import template from './template.html';
import UsesTranslator from '../../Mixins/usesTranslator';

require('./style.less');

export default {
  template: template,
  mixins: [UsesTranslator],
  props: {
    comment: {
      type: Object,
      default: null
    }
  },
  methods: {
    cancel() {
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
