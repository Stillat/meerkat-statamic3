class Convert {

  /**
   * Attempts to convert the provided value to an integer.
   *
   * @param {String|number|null} val The value to convert.
   * @returns {number}
   */
  static toInt(val) {
    if (typeof val === 'undefined' || val === null) {
      return 0;
    }

    let result = parseInt(val, 10);

    if (isNaN(result)) {
      return 0;
    }

    return result;
  }

}

export default Convert;
