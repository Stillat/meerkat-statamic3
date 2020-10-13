import template from './template.html';
import Comment from '../../../Data/Comments/comment';
import CommentActions from '../CommentActions';
import ReplyAuthor from '../ReplyToAuthorDisplay';
import {parseMarkdown} from '../../markdown';
import UsesTranslator from '../../Mixins/usesTranslator';

require('./style.less');

export default {
  template: template,
  mixins: [UsesTranslator],
  components: {
    'comment-actions': CommentActions,
    'reply-author': ReplyAuthor
  },
  props: {
    permissions: {
      type: Object,
      default: null,
      required: true
    },
    avatarDriver: {
      type: String,
      default: ''
    },
    comment: {
      type: Comment,
      default: null
    },
    actionsDisabled: {
      type: Boolean,
      default: false
    },
    displayThread: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    forceDismiss() {
      this.$refs.actions.forceDismiss();
    },
    parseMarkdown: parseMarkdown
  }
};
