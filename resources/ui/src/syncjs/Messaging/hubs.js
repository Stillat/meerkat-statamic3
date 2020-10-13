import Hub from './hub';
import Type from '../Types/type';
import String from '../Types/string';
import Guid from '../Types/guid';

/**
 * Provides a dynamic wrapper for managing shared hub instances.
 *
 * Once a hub has been registered, it can be accessed like so:
 *
 * Hubs.hubName().someHubMethod()
 */
class Hubs {

  constructor() {
    return new Proxy(this, {
      get: function (object, property) {
        if (Reflect.has(object, property)) {
          return Reflect.get(object, property);
        }

        return function methodMissing() {
          if (typeof Hubs.Registered[property] !== 'undefined') {
            return Hubs.Registered[property];
          }

          throw new Error('Could not locate hub: ' + property);
        };
      }
    });
  }

  getIdentifier() {
    return Hubs.GlobalIdentifier;
  }

  make(name, typeNamespace) {
    let hub = new Hub(name, typeNamespace);

    if (String.hasValue(typeNamespace)) {
      if (Type.hasValue(Hubs.TypedHubs[typeNamespace]) === false) {
        Hubs.TypedHubs[typeNamespace] = [];
      }

      Hubs.TypedHubs[typeNamespace].push(hub);
    }

    Hubs.Registered[name] = hub;
  }

  /**
   * Returns any hubs registered for the provided type.
   *
   * @param {string} type The type name.
   * @returns {Array<Hub>}
   */
  getTypedHubs(type): Array<Hub> {
    if (Type.hasValue(Hubs.TypedHubs[type])) {
      return Hubs.TypedHubs[type];
    }

    return [];
  }

}

Hubs.GlobalIdentifier = Guid.newGuid();
Hubs.Registered = {};
Hubs.TypedHubs = {};

export default new Hubs();
