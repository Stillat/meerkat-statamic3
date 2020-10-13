import Environment from '../Config/environment';
import Blueprints from './blueprints';
import Notifications from './Notifications';

class ControlPanel {

  constructor() {
    this._blueprints = new Blueprints();
    this._notif = new Notifications();
  }

  blueprints(): Blueprints {
    return this._blueprints;
  }

  message(): Notifications {
    return this._notif;
  }

  /**
   * Generates a Statamic Control Panel URL.
   *
   * @param path
   * @returns {string}
   */
  static cpUrl(path) {
    return Environment.StatamicCpRoot + path;
  }

}

export default ControlPanel;
