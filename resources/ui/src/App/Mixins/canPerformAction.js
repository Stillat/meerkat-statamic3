import Type from '../../Types/type';
import ActionState from '../actionState';

export default {
  methods: {
    performAction(action, comment) {
      if (Type.hasValue(this.handlers[action])) {
        this.confirm(new this.handlers[action](comment))
          .onConfirm((state: ActionState) => {
            state.proceed();
          });
      }
    }
  }
};
