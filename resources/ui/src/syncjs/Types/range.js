import Type from './type';

class Range {

  /**
   * Generates a range between the provided min and maximum.
   *
   * @param {Number} min Where the range should start.
   * @param {Number} max Where the range should end.
   * @param {Number} step The amount to increment each range element. Default: 1.
   * @returns {Array<Number>}
   */
  static get(min: Number, max: Number, step: Number): Array<Number> {
    step = Type.withDefault(step, 1);

    let range = [];

    for (let i = min; i <= max; i += step) {
      range.push(i);
    }

    return range;
  }

}

export default Range;
