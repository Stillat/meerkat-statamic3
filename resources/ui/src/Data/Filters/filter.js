import SearchOptions from '../Comments/searchOptions';
import Guid from '../../Types/guid';
import Comment from '../Comments/comment';
import OverviewResponse from '../../Http/Responses/Reporting/overviewResponse';

class Filter {
  constructor() {
    this.id = Guid.newGuid();
    this.internalName = '';
    this.name = '';

    this.count = 0;
    this.countDisplay = '';
    this.query = {};
    this.filters = [];
  }

  adjustOptions(options: SearchOptions) {
    options.query = Object.assign({}, this.query, options.query);

    options.query.filter = this.filters.join('|');

    return options;
  }

  /**
   * Determines if the filter should reload based on the comment.
   *
   * @param {Array<Comment>} comments The comment to test.
   * @returns {boolean}
   */
  shouldReload(comments: Array<Comment>): Boolean {
    return false;
  }

  updateState(report : OverviewResponse) {
  }

}

export default Filter;
