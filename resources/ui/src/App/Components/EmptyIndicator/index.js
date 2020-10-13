import template from './template.html';
import UsesTranslator from '../../Mixins/usesTranslator';

require('./style.less');

export default {
  template: template,
  mixins: [UsesTranslator],
  props: {
    totalCount: {
      type: Number,
      default: 0
    }
  }
};
