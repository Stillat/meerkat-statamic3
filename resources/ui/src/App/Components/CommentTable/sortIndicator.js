import template from './sortIndicator.html';

require('./sortIndicator.less');

export default {
  template: template,
  props: {
    column: {
      type: String,
      default: null
    },
    manager: {
      type: Object,
      default: null
    },
    respondToClick: {
      type: Boolean,
      default: true
    }
  },
  methods: {
    updateManager() {
      if (this.respondToClick === true) {
        this.manager.toggleColumn(this.column);
        this.$emit('order-changed');
      }
    }
  }
};
