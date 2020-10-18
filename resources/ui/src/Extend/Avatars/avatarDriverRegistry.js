import Type from '../../Types/type';
import Environment from '../../Config/environment';

class AvatarDriverRegistry {

  /**
   * Registers the Avatar drivers with the VueJS and CoreJS runtime.
   */
  static registerDriversWithRunTime() {
    for (let driver in AvatarDriverRegistry.Drivers) {
      let driverComponent = AvatarDriverRegistry.Drivers[driver];

      Environment.ContextVueJs.component(driver, driverComponent);
    }
  }

  /**
   * Generates an internal driver name.
   *
   * @param {string} name The driver name.
   * @returns {string}
   */
  static getDriverName(name): String {
    return 'meerkat_avatarDriver_' + name;
  }

  /**
   * Registers a new avatar driver.
   *
   * @param {String} driverName The driver name.
   * @param {String} displayName A user-friendly name for the driver.
   * @param {Object} driverComponent The driver component.
   */
  static registerDriver(driverName, displayName, driverComponent) {
    let newDriverName = AvatarDriverRegistry.getDriverName(driverName);

    AvatarDriverRegistry.DriverMapping[driverName] = displayName;
    AvatarDriverRegistry.Drivers[newDriverName] = driverComponent;
    AvatarDriverRegistry.DisplayNames[newDriverName] = displayName;
  }

  /**
   * Sets the Avatar driver mapping.
   *
   * @param {Object} drivers The driver mapping to set.
   */
  static setDrivers(drivers) {
    AvatarDriverRegistry.Drivers = drivers;
  }

  /**
   * Sets the Avatar driver display name mapping.
   *
   * @param {Object} displayNames The display names.
   */
  static setDisplayNames(displayNames: Object) {
    AvatarDriverRegistry.DisplayNames = displayNames;
  }

  /**
   * Gets the Avatar driver display name mapping.
   *
   * @returns {Object}
   */
  static getDisplayNames(): Object {
    return AvatarDriverRegistry.DisplayNames;
  }

  /**
   * Gets the avatar driver mapping.
   *
   * @returns {Object}
   */
  static getDrivers(): Object {
    return AvatarDriverRegistry.Drivers;
  }

  /**
   * Tests if a driver with the provided name has been registered.
   *
   * @param {string} driverName The driver name.
   * @returns {boolean}
   */
  static hasDriver(driverName): Boolean {
    return Type.hasValue(AvatarDriverRegistry.Drivers[AvatarDriverRegistry.getDriverName(driverName)]);
  }

}

AvatarDriverRegistry.DefaultDriverName = 'initials';
AvatarDriverRegistry.DisplayNames = {};
AvatarDriverRegistry.Drivers = {};
AvatarDriverRegistry.DriverMapping = {};

export default AvatarDriverRegistry;
