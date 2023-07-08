import jQuery from '../Types/jQuery';
import Settings from './settings';
import Type from '../Types/type';
import String from '../Types/string';
import DefaultPermissionSet from '../Data/defaultPermissionSet';

class Environment {

  static window() {
    return Environment.CONTEXT_WINDOW;
  }

  /**
   * @param {string} selector
   * @returns {*}
   */
  static $(selector: string): jQuery {
    return Environment.ContextJquery.apply(null, arguments);
  }

  /**
   * Moves the visible window to the top of the document.
   */
  static scrollTop() {
    window.scrollTo(0, 0);
  }

  /**
   * Returns the current user's permission set.
   *
   * @returns {Object|string|number}
   */
  static getPermissions(): Object {
    return Type.withDefault(Environment.UserContext, DefaultPermissionSet);
  }

  /**
   * Tests if Control Panel configuration has been enabled.
   *
   * @returns {boolean}
   */
  static isControlPanelConfigEnabled(): Boolean {
    let curValue = Type.withDefault(Environment.Settings['controlPanelConfigurationEnabled'], true);

    return (curValue === true);
  }

  static getCsrfToken(): string {
    return window.Statamic.$config.get('csrfToken');
  }

  static pushHistoryState(relativeUrl) {
    if (window.history.pushState) {
      let fullUrl = String.finish(Environment.StatamicCpRoot, '/') + 'meerkat/' + relativeUrl;

      window.history.pushState({urlPath: fullUrl}, '', fullUrl);
    }
  }

}

Environment.MarkdownHandler = null;
Environment.Settings = new Settings();
Environment.UserContext = null;
Environment.UserPreferences = {
  'cp_avatar_driver': 'initials',
  'cp_per_page': 10
};
Environment.Preferences = null;

Environment.StatamicApiRoot = '';
Environment.StatamicCpRoot = '';
Environment.ContextJquery = null;
Environment.ContextVueJs = null;
Environment.ContextComponentRegister = null;

export default Environment;
