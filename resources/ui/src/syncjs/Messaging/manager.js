import Environment from '../environment';
import Type from '../Types/type';
import Message from './message';
import EventEmitter from 'wolfy87-eventemitter';
import String from '../Types/string';

const store = require('store');

class Manager extends EventEmitter {

  constructor(canReceiveEvents) {
    super();

    this.emitToSelf = false;

    if (canReceiveEvents && Type.hasValue(Environment.ContextJquery)) {
      Environment.ContextJquery(window).on('storage', this.handleMessageReceived.bind(this));
    }
  }

  /**
   * Broadcasts a message to all other listeners.
   *
   * @param {Message} message The message to broadcast.
   */
  broadcast(message: Message) {
    let broadcastKey = Manager.StorageMessageKey;

    if (Type.hasValue(this['getMessageKey']) === true) {
      broadcastKey = this.getMessageKey();
    }

    this.broadcastWithKey(broadcastKey, message);
  }

  broadcastWithKey(broadcastKey: string, message: Message) {
    let messageData = message.toMessageString();

    store.set(broadcastKey, messageData);
    store.remove(broadcastKey);

    if (this.emitToSelf === true && Type.hasValue(this['selfHandle'])) {
      this.selfHandle(broadcastKey, message);
    }
  }

  /**
   * Constructs a message and broadcasts it using the global manager.
   *
   * @param {string} eventName The message name.
   * @param {Object|string} eventData The message payload.
   */
  static broadcastAll(eventName: string, eventData: Object) {
    Manager.Instance.broadcast(Message.fromData(eventName, eventData));
  }

  /**
   * Constructs a message and broadcasts it to the current listener.
   *
   * @param {string} eventName The message name.
   * @param {Object|string} eventData The message payload.
   */
  selfBroadcast(eventName: string, eventData: string) {
    this.emit(Manager.EventMessageReceived, Message.fromData(eventName, eventData));
  }

  /**
   * Handles the window storage event and redirects appropriate messages to the listeners.
   *
   * @param windowEvent
   */
  handleMessageReceived(windowEvent) {
    if (Type.hasValue(windowEvent) && Type.hasValue(windowEvent['originalEvent'])) {
      if (String.startsWith(windowEvent.originalEvent.key, Manager.StorageMessageKey) &&
        Type.hasValue(windowEvent.originalEvent['newValue'])) {

        try {
          let parsedMessage = JSON.parse(JSON.parse(windowEvent.originalEvent.newValue));

          if (this.emitToSelf === true && Type.hasValue(this['selfHandle'])) {
            this.selfHandle(windowEvent.originalEvent.key, parsedMessage);
          }

          this.emit(Manager.EventMessageReceived, parsedMessage);
        } catch (err) {
          throw err;
        }
      }
    }
  }

}

Manager.StorageMessageKey = '_broadcastMessage';
Manager.StorageNamespaceMessageKey = '_broadcastNamespace';
Manager.EventMessageReceived = 'message.received';
Manager.Instance = new Manager(false);

export default Manager;
