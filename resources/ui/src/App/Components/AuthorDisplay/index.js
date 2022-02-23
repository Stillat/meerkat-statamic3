require('./style.less');

import template from './template.html';
import Author from '../../../Data/Comments/author';
import Comment from '../../../Data/Comments/comment';

export default {
  template: template,
  props: {
    comment: {
      type: Object,
      default: null
    },
    author: {
      type: Object,
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
