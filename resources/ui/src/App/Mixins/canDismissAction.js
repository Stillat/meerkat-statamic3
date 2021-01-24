export default {
  methods: {
    forceDismiss() {
      if (this.currentAction !== null) {
        this.currentAction.dismiss();
      }
    },
    checkForDismiss() {
      if (this.currentAction !== null && this.currentAction.display === true && this.currentAction.canDismiss()) {
        this.currentAction.dismiss();
      }
    }
  }
};
