import Type from './type';

class String {

  /**
   * Limits the value's length.
   *
   * @param {string} value The value to limit.
   * @param {Number} limit The maximum number of characters of the value.
   * @param {string} cap An optional end cap. Defaults to `...`.
   * @returns {string}
   */
  static truncate(value: string, limit: Number, cap: string): string {
    if (Type.hasValue(cap) === false) {
      cap = '...';
    }

    if (value.length > limit) {
      return value.substr(0, limit - 1) + cap;
    }

    return value;
  }

  /**
   * Tests if the value has a string value.
   *
   * @param {string|null} value The value to test.
   * @returns {boolean}
   */
  static hasValue(value) {
    if (Type.hasValue(value) === false) {
      return false;
    }

    if (value.constructor.name !== 'String') {
      return false;
    }

    return value.trim().length !== 0;
  }

  /**
   * Returns the value, or the default if no value set.
   *
   * @param {string} value The value to test.
   * @param {string} defaultValue The value to return if no value present.
   * @returns {string|*}
   */
  static withDefault(value, defaultValue: string): string {
    if (String.hasValue(value)) {
      return value;
    }

    return defaultValue;
  }

  /**
   * Ensures the value ends with the suffix.
   *
   * @param {string} value
   * @param {string} suffix
   * @returns {string}
   */
  static finish(value: string, suffix: string): string {
    if (String.endsWith(value, suffix)) {
      return value;
    }

    return value + suffix;
  }

  /**
   * Ensures the value begins with the prefix.
   *
   * @param {string} value
   * @param {string} prefix
   * @returns {string}
   */
  static start(value: string, prefix: string): string {
    if (String.startsWith(value, prefix)) {
      return value;
    }

    return prefix + value;
  }

  /**
   * Indicates if the value string starts with the value.
   *
   * @param {string} value
   * @param {string} prefix
   * @returns {string}
   */
  static startsWith(value: string, prefix: string): string {
    return (value.substr(0, prefix.length) === prefix);
  }

  /**
   * Indicates if the value string ends with the suffix.
   *
   * @param {string} value
   * @param {string} suffix
   * @returns {string}
   */
  static endsWith(value: string, suffix: string): string {
    return (value.substr(value.length - suffix.length) === suffix);
  }

  /**
   * Formats the provided value with the replacements.
   *
   * @param {string} value
   * @param {array|string} replacements
   */
  static format(value, ...replacements) {
    let content = value;

    for (let i = 0; i < replacements.length; i++) {
      let replacement = '{' + i + '}';

      content = content.replace(replacement, replacements[i]);
    }

    return content;
  }

  /**
   * Transforms the value's first character to upper-cased.
   *
   * @param {string} value The value to transform.
   * @returns {string}
   */
  static ucFirst(value: string): string {
    if (String.hasValue(value) === false) {
      return value;
    }

    if (value.length === 1) {
      return value.toUpperCase();
    }

    return value.charAt(0).toUpperCase() + value.slice(1);
  }

}

export default String;
