import Type from '../Types/type';
import Hubs from './hubs';
import Manager from './manager';

export function reactsToType(instance, options) {
  if (Type.hasValue(options) === false || Type.withDefault(options['identity'], null) === null) {
    return;
  }

  instance.__syncJsType = Type.typeOf(instance);
  instance.__syncJsIdentityField = options.identity;
  instance.__syncJsTypeNamespace = Manager.StorageNamespaceMessageKey + '@' + instance.__syncJsType;
  instance.__syncJsGetIdentity = function () {
    return Type.withDefault(this[options.identity], null);
  }.bind(instance);

  instance.__syncJsTriggerFromTypeHandle = function (typeHandler) {
    if (Type.isFunction(this[typeHandler])) {
      this[typeHandler]();
    }
  }.bind(instance);

  instance.__syncJsTriggerFromTypeHandleWithObjParam = function (typeHandler, objParam) {
    if (Type.isFunction(this[typeHandler])) {
      this[typeHandler](objParam);
    }
  }.bind(instance);

  let typedHubs = Hubs.getTypedHubs(instance.__syncJsType);

  for (let i = 0; i < typedHubs.length; i += 1) {
    typedHubs[i].typeHandlers.push(instance);
  }
}
