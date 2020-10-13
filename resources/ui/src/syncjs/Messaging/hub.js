import Manager from './manager';
import Message from './message';
import Type from '../Types/type';
import String from '../Types/string';
import HandlerManager from './handlerManager';

/**
 * The Hub is a dynamic wrapper around the Manager.
 */
class Hub extends Manager {

  constructor(name: string, typeNamespace: string) {
    super(true);

    this.emitToSelf = true;
    this.name = name;
    this.reactNamespace = Type.withDefault(typeNamespace, null);
    this.receiverKey = this.getMessageKey();
    this.handlerManagers = [];
    this.typeHandlers = [];

    this.handlePrefix = 'on' + String.ucFirst(this.name.toLowerCase());
    this.reactPrefix = null;

    if (this.reactNamespace !== null) {
      this.reactPrefix = 'on' + String.ucFirst(this.reactNamespace.toLowerCase());
    }

    return new Proxy(this, {
      get: function (object, property) {
        if (Reflect.has(object, property)) {
          return Reflect.get(object, property);
        }

        return function methodMissing() {
          let message = new Message(property, ...arguments);

          this.broadcast(message);
        }.bind(this);
      }.bind(this)
    });
  }

  /**
   * Registers a message handler with the hub.
   *
   * @param {Object} handler The handler to receive messages.
   */
  handledBy(handler: Object) : HandlerManager {
    let handlerManager = new HandlerManager(this, handler);

    this.handlerManagers.push(handlerManager);

    return handlerManager;
  }

  /**
   * Generates a custom message key for this hub.
   *
   * @returns {string}
   */
  getMessageKey() {
    return Manager.StorageMessageKey + ':hub:' + this.name;
  }

  /**
   * Checks if the received message should be handled by this hub.
   *
   * If the message should be handled, any hub handlers will be called.
   *
   * @param {string} key The broadcast key.
   * @param {Message} message The message that was sent.
   */
  selfHandle(key, message) {
    if (key === this.receiverKey) {
      let messageName = String.ucFirst(message.name),
        handleName = this.handlePrefix + messageName;

      if (this.reactNamespace !== null && Type.isArray(message.data)) {
        let handleName = this.reactPrefix + messageName;

        for (let i = 0; i < message.data.length; i += 1) {
          if (typeof message.data[i] === 'object' || String.hasValue(message.data[i])) {
            if (this.typeHandlers.length > 0) {
              for (let j = 0; j < this.typeHandlers.length; j += 1) {
                let idField = this.typeHandlers[j].__syncJsIdentityField;

                if (typeof message.data[i] === 'object') {
                  if (Type.hasValue(message.data[i][idField])) {
                    if (message.data[i][idField] === this.typeHandlers[j].__syncJsGetIdentity()) {
                      this.typeHandlers[j].__syncJsTriggerFromTypeHandleWithObjParam(
                        handleName, message.data[i]
                      );
                    }
                  }
                } else {
                  if (this.typeHandlers[j].__syncJsGetIdentity() === message.data[i]) {
                    this.typeHandlers[j].__syncJsTriggerFromTypeHandle(handleName);
                  }
                }
              }
            }
          }
        }
      }

      for (let i = 0; i < this.handlerManagers.length; i += 1) {
        this.handlerManagers[i].triggerHandler(handleName, message);
      }
    }
  }

}

export default Hub;
