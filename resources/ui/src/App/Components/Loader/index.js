import template from './template.html';
import Type from '../../../Types/type';

require('./style.less');

export default {
  template: template,
  props: {
    displayText: {
      type: String,
      default: ''
    },
    size: {
      type: Number
    },
    displayInline: {
      type: Boolean,
      default: false
    },
    color: {
      type: String,
      default: '#737f8c'
    }
  },
  computed: {
    computedSize() {
      if (Type.hasValue(this.size)) {
        return this.size;
      }

      return this.displayInline ? 16 : 24;
    }
  }
};
