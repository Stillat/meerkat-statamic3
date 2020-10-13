import {controlPanelHooks} from '../hooks';
import ControlPanel from './../Statamic/controlPanel';
import Bootstrapper from './bootstrapper';

/**
 * Provides utilities for interacting with the Statamic Control Panel
 * and bootstrapping Meerkat specific applications and components.
 */
class ControlPanelApplication {

  constructor() {
    this.cpHooks = controlPanelHooks;
    this.controlPanel = new ControlPanel();
  }

  boot() {
    this.runCpHooks();

    Bootstrapper.bootstrapApplications();
  }

  runCpHooks() {
    let pathName = window.location.pathname;

    for (let i = 0; i < this.cpHooks.length; i += 1) {
      if (this.cpHooks[i].path.test(pathName)) {
        this.cpHooks[i].uses.run(this);
      }
    }
  }

  /**
   * Returns the global Control Panel application.
   *
   * @returns {ControlPanelApplication}
   */
  static current(): ControlPanelApplication {
    return ControlPanelApplication.Instance;
  }

  /**
   * Returns the current Control Panel instance.
   *
   * @returns {ControlPanel}
   */
  static controlPanel(): ControlPanel {
    return ControlPanelApplication.Instance.controlPanel;
  }

}

/**
 * The current Control Panel instance.
 *
 * @type {ControlPanelApplication|null}
 */
ControlPanelApplication.Instance = null;

export default ControlPanelApplication;
