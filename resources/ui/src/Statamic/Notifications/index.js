/**
 * Provides utilities for interacting with Statamic's Control Panel toast bus.
 */
class Notifications {

  /**
   * Displays a success message in the Statamic Control Panel.
   *
   * @param {string} message The message to display.
   * @param {Object} options The message options.
   */
  success(message, options) {
    window.Statamic.$toast.success(message, options);
  }

  /**
   * Displays an information message in the Statamic Control Panel.
   *
   * @param {string} message The message to display.
   * @param {Object} options The message options.
   */
  info(message, options) {
    window.Statamic.$toast.info(message, options);
  }

  /**
   * Displays an error message in the Statamic Control Panel.
   *
   * @param {string} message The message to display.
   * @param {Object} options The message options.
   */
  error(message, options) {
    window.Statamic.$toast.error(message, options);
  }

}

export default Notifications;
