import Filter from './filter';
import trans from '../../trans';
import OverviewResponse from '../../Http/Responses/Reporting/overviewResponse';
import {App} from '../../App';
import Comment from '../Comments/comment';

class IsSpamFilter extends Filter {

  constructor() {
    super();

    this.id = 1;
    this.internalName = 'spam';
    this.name = trans('filters.spam');
    this.filters = [
      'is:spam(true)'
    ];
  }

  updateState(report: OverviewResponse) {
    this.count = report.totalSpam;
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
      if (comments[i].hasBeenCheckedForSpam === false || comments[i].isSpam === false) {
        return true;
      }
    }

    return false;
  }

}

export default IsSpamFilter;
