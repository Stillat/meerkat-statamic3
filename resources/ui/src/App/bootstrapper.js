import Environment from '../Config/environment';
import {Guid, Type} from './../Types/common';
import Translator from '../Translation/translator';
import StatamicTranslator from '../Statamic/statamicTranslator';
import AvatarDriverRegistry from '../Extend/Avatars/avatarDriverRegistry';
import {registerVueFilters} from './registerVueFilters';
import {registerVueComponents} from './registerVueComponents';
import UserSettings from './userSettings';

/**
 * Provides utilities for bootstrapping Meerkat applications and components.
 */
class Bootstrapper {

  static registerVueJsDependencies() {
    if (Type.hasValue(Environment.ContextVueJs)) {
      registerVueFilters(Environment.ContextVueJs);
      registerVueComponents(Environment.ContextVueJs);
    }
  }

  /**
   * Registers extensibility drivers from the global window state.
   */
  static liftExtensibilityDrivers() {
    if (typeof window[Bootstrapper.ExtensibilityInstance] !== 'undefined') {
      /**
       * We will locate any existing extensibility objects stored in the
       * temporary `meerkatExtend` environment. We do not want to keep
       * that environment around, so we will destroy it afterwards.
       */
      let extendInstance = window[Bootstrapper.ExtensibilityInstance]['Extend'],
        existingDrivers = extendInstance.Avatars.getDrivers(),
        existingDisplayNames = extendInstance.Avatars.getDisplayNames();

      AvatarDriverRegistry.setDisplayNames(existingDisplayNames);
      AvatarDriverRegistry.setDrivers(existingDrivers);

      delete window[Bootstrapper.ExtensibilityInstance];

      AvatarDriverRegistry.registerDriversWithRunTime();
    }

  }

  /**
   * Registers the Meerkat UI core dependencies, such as the Translator.
   */
  static registerDependencies() {
    Translator.Instance = new StatamicTranslator();
  }

  /**
   * Analyzes the DOM for any elements containing Meerkat application requests.
   */
  static bootstrapApplications() {
    Environment.Preferences = new UserSettings();

    Bootstrapper.registerDependencies();
    Bootstrapper.liftExtensibilityDrivers();
    Bootstrapper.registerVueJsDependencies();

    let appElements = Environment.$('[data-meerkat-app]');

    if (appElements.length > 0) {
      for (let i = 0; i < appElements.length; i += 1) {
        let elementHost = Environment.$(appElements[i]);

        Bootstrapper.runApp(elementHost.data('meerkat-app'), elementHost);
      }
    }
  }

  static runApp(appName, elementHost) {
    if (typeof Bootstrapper.AppMap[appName] !== 'undefined') {
      let appType = Bootstrapper.AppMap[appName],
        instanceId = Guid.newGuid(),
        appId = 'app-' + instanceId;

      elementHost.attr('data-meerkat-application', instanceId);
      elementHost.attr('id', appId);

      appType.el = '#' + appId;

      Bootstrapper.Instances[instanceId] = new Environment.ContextVueJs(appType);
    }
  }

}

Bootstrapper.ExtensibilityInstance = 'meerkatExtend';
Bootstrapper.Instances = {};
Bootstrapper.AppMap = {};

export default Bootstrapper;
