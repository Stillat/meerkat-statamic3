import Environment from '../Config/environment';
import Blueprints from './blueprints';
import Notifications from './Notifications';
import Addons from './addons';

class ControlPanel {

  constructor() {
    this._blueprints = new Blueprints();
    this._notif = new Notifications();
    this._addons = new Addons();
  }

  blueprints(): Blueprints {
    return this._blueprints;
  }

  addons(): Addons {
    return this._addons;
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
