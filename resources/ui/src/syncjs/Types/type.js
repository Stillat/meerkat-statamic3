class Type {

  /**
   * Tests if the supplied value is an array.
   *
   * @param {object} obj The object to test.
   * @returns {boolean}
   */
  static isArray(obj: Object): Boolean {
    if (Type.hasValue(obj) === false) {
      return false;
    }

    return Array.isArray(obj);
  }

  /**
   * Tests if the object is a function.
   *
   * @param {object} obj The object to test.
   * @returns {boolean}
   */
  static isFunction(obj): Boolean {
    return typeof obj === 'function';
  }

  /**
   * Attempts to determine the type of the provided value.
   *
   * @param {(Object|string)} type The type to check.
   * @returns {null|String}
   */
  static typeOf(type): String {
    if (typeof type === 'undefined') {
      return null;
    }

    if (typeof type.prototype !== 'object') {
      if (typeof type.__proto__ === 'object') {
        return type.__proto__.constructor.name;
      }

      return null;
    }

    if (typeof type.prototype.constructor !== 'function') {
      return null;
    }

    return type.prototype.constructor.name;
  }

  /**
   * Tests if the provided value is of the specified type.
   *
   * @param {Object|string} value The value to test.
   * @param {Object|string} type The type to guarantee.
   * @returns {boolean}
   */
  static isTypeOf(value, type): Boolean {
    return Type.typeOf(value) === Type.typeOf(type);
  }

  /**
   * Tests if the provided value has a value set.
   *
   * @param {(Object|string|number)} value The value to test.
   * @returns {boolean}
   */
  static hasValue(value) {
    if (typeof value === 'undefined') {
      return false;
    }

    return value !== null;
  }

  /**
   * Tests if all the provided value paths are set.
   *
   * @param {Array<Object|string|number>} values The paths to test.
   * @returns {boolean}
   */
  static hasAllValues(values: Array) {
    for (let i = 0; i < values.length; i += 1) {
      if (Type.hasValue(values[i]) === false) {
        return false;
      }
    }

    return true;
  }

  /**
   * Returns the value, or the default if no value set.
   *
   * @param {(Object|string|number)} value The value to test.
   * @param {(Object|string|number)} defaultValue The default value, if any.
   * @returns {(Object|string|number)}
   */
  static withDefault(value, defaultValue) {
    if (Type.hasValue(value)) {
      return value;
    }

    return defaultValue;
  }

}

export default Type;
