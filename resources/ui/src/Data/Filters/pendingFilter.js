import Filter from './filter';
import trans from '../../trans';
import OverviewResponse from '../../Http/Responses/Reporting/overviewResponse';
import {App} from '../../App';
import Comment from '../Comments/comment';

class PendingFilter extends Filter {

  constructor() {
    super();

    this.id = 2;
    this.internalName = 'pending';
    this.name = trans('filters.pending');
    this.filters = [
      'where(spam, !==, true)',
      'is:published(false)'
    ];
  }

  updateState(report : OverviewResponse) {
    this.count = report.pending;
    this.countDisplay = App.NumberFormatter.abbreviate(this.count);
  }

  /**
   * Determines if the filter should reload based on the comment.
   *
   * @param {Array<Comment>} comments The comment to test.
   * @returns {boolean}
   */
  shouldReload(comments: Array<Comment>): Boolean {
    for (let i = 0; i < comments.length; i += 1) {
      if (comments[i].published === true) {
        return true;
      }
    }

    return false;
  }

}

export default PendingFilter;
