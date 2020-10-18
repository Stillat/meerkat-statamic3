import Type from '../../Types/type';
import EntryBehavior from './entryBehavior';

class ConfigItem {

  constructor() {
    this.namespace = '';
    this.runtimeValue = '';
    this.key = '';
    this.behavior = EntryBehavior.Managed;
    this.defaults = [];
    this.value = null;
  }

  /**
   * Constructs a new ConfigItem instance from an API object.
   *
   * @param {Object} apiObject The API object.
   * @returns {ConfigItem}
   */
  static fromApiObject(apiObject): ConfigItem {
    let item = new ConfigItem();

    item.namespace = Type.withDefault(apiObject[ConfigItem.ApiNamespace], '');
    item.key = Type.withDefault(apiObject[ConfigItem.ApiKey], '');
    item.behavior = Type.withDefault(apiObject[ConfigItem.ApiBehavior], EntryBehavior.Managed);
    item.defaults = Type.withDefault(apiObject[ConfigItem.ApiDefaults], []);
    item.value = Type.withDefault(apiObject[ConfigItem.ApiValue], null);
    item.runtimeValue = item.namespace + '.' + item.key;

    return item;
  }

}

ConfigItem.ApiNamespace = 'namespace';
ConfigItem.ApiKey = 'key';
ConfigItem.ApiBehavior = 'behavior';
ConfigItem.ApiDefaults = 'defaults';
ConfigItem.ApiValue = 'value';

export default ConfigItem;
