import ConfigItem from './configItem';
import SpamGuard from './spamGuard';

class GuardMapper {

  /**
   * Maps the spam guard configuration to the appropriate configuration item locations.
   *
   * @param {Array<ConfigItem>} items The configuration items.
   * @param {Array<SpamGuard>} guards The current spam guard configuration.
   */
  static mapGuards(items: Array<ConfigItem>, guards: Array<SpamGuard>) {
    let newGuards = [];

    for (let i = 0; i < guards.length; i++) {
      let currentGuard = guards[i];

      if (currentGuard.enabled === true) {
        newGuards.push(currentGuard.class);
      }
    }

    items[GuardMapper.SpamGuards].value = newGuards;

    return items;
  }

}

GuardMapper.SpamGuards = 'publishing.guards';

export default GuardMapper;

