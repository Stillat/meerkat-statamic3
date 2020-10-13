import {Hubs} from './index';

class Message {
  constructor(name: string, eventData: Object) {
    this.name = name;
    this.data = eventData;
    this.origin = Hubs.getIdentifier();
  }

  /**
   * Constructs a message from the provided details.
   *
   * @param {string} eventName The message name.
   * @param {Object|string} eventData The message payload.
   *
   * @returns {Message}
   */
  static fromData(eventName: string, eventData: Object): Message {
    let message = new Message();

    message.name = eventName;
    message.data = eventData;
    message.origin = Hubs.getIdentifier();

    return message;
  }

  /**
   * Converts the message to a payload string.
   *
   * @returns {string}
   */
  toMessageString() {
    return JSON.stringify({
      name: this.name,
      data: this.data,
      origin: this.origin
    });
  }
}

export default Message;
