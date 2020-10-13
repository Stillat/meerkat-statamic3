import ActionState from '../actionState';

export default {
  methods: {
    getHandler() {
      return this.currentAction;
    },
    closeHandler() {
      if (this.currentAction !== null) {
        this.currentAction.display = false;
        this.currentAction = null;
      }
    },
    confirm(handler: ActionState): ActionState {
      this.currentAction = handler;
      this.currentAction.display = true;

      /** Automatically clean up the action state. */
      handler.onCancel(function () {
        this.currentAction.display = false;
        this.currentAction = null;
      }.bind(this));

      return handler;
    }
  }
};
