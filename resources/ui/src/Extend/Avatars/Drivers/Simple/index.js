import template from './template.html';

export default {
  template: template,
  props: {
    author: {
      type: Object,
      default: null
    },
    isRounded: {
      type: Boolean,
      default: true
    }
  }
};
