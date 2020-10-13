import Author from '../../../Data/Comments/author';
import template from './template.html';
import UsesTranslator from '../../Mixins/usesTranslator';

require('./style.less');

export default {
  mixins: [UsesTranslator],
  template: template,
  props: {
    author: {
      type: Author,
      default: null
    },
    avatarDriver: {
      type: String,
      default: null
    }
  },
  methods: {
    getAuthor() {
      return {
        author: this.author
      };
    }
  }
};
