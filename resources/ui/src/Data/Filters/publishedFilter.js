import Filter from './filter';
import trans from '../../trans';
import OverviewResponse from '../../Http/Responses/Reporting/overviewResponse';
import {App} from '../../App';
import Comment from '../Comments/comment';

class PublishedFilter extends Filter {

  constructor() {
    super();

    this.id = 3;
    this.internalName = 'published';
    this.name = trans('filters.published');
    this.filters = [
      'is:published(true)'
    ];
  }

  updateState(report : OverviewResponse) {
    this.count = report.totalPublished;
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
      if (comments[i].published === false) {
        return true;
      }
    }

    return false;
  }

}

export default PublishedFilter;
