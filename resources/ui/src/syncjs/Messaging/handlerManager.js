import Type from '../Types/type';
import {Message} from './message';
import {Hubs} from './index';

class HandlerManager {

  constructor(hub, handler) {
    this.hub = hub;
    this.handler = handler;
    this.methodRedirects = [];
    this.reactsToCurrentSyncInstance = true;
  }

  /**
   * Tests if the manager has method redirects.
   *
   * @returns {boolean}
   */
  hasRedirects(): Boolean {
    return this.methodRedirects.length > 0;
  }

  /**
   * Tests if the handler has the requested method.
   *
   * @param method
   */
  hasMethod(method): Boolean {
    return Type.isFunction(this.handler[method]);
  }

  /**
   * Adds the method to the internal redirects list.
   *
   * @param {string} method The method name.
   */
  redirectTo(method) {
    this.methodRedirects.push(method);
  }

  reactsToInstance(doesReact): HandlerManager {
    this.reactsToCurrentSyncInstance = doesReact;

    return this;
  }

  /**
   * Removes all previously registered method redirects.
   */
  clearRedirects() {
    this.methodRedirects = [];
  }

  /**
   * Invokes the provided method by name
   *
   * @param {string} methodName The method name.
   * @param {data} data The data to supply as the first argument.
   * @private
   */
  _invokeMethod(methodName, data) {
    this.handler[methodName](data);
  }

  triggerRedirects(message: Message) {
    if (this.hasRedirects()) {
      for (let i = 0; i < this.methodRedirects.length; i += 1) {
        this.methodRedirects[i](message.data);
      }
    }
  }

  triggerHandler(handlerName: string, message: Message) {
    if (this.reactsToCurrentSyncInstance === false) {
      if (message.origin === Hubs.getIdentifier()) {
        return;
      }
    }

    this.triggerRedirects(message);

    if (this.hasMethod(handlerName)) {
      this._invokeMethod(handlerName, message.data);
    }
  }

}

export default HandlerManager;
