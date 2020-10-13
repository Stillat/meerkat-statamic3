require('./style.less');

import template from './template.html';
import Author from '../../../Data/Comments/author';
import Comment from '../../../Data/Comments/comment';

export default {
  template: template,
  props: {
    comment: {
      type: Comment,
      default: null
    },
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
