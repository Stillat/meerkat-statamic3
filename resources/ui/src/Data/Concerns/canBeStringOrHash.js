import md5 from 'crypto-js/md5';

/**
 * @property {function() : String} toJsonString() Returns the object to a JSON-encoded string.
 * @property {function() : String} toHash() Returns the object as a hash string.
 */

export function hash(value) {
  return md5(value).toString();
}

export function canBeStringOrHash(instance) {

  instance.toJsonString = function () {
    return JSON.stringify(this);
  }.bind(instance);

  instance.toHash = function () {
    return md5(this.toJsonString()).toString();
  }.bind(instance);
}
