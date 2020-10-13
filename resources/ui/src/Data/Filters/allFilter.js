import Filter from './filter';
import trans from '../../trans';
import OverviewResponse from '../../Http/Responses/Reporting/overviewResponse';
import {App} from '../../App';

class AllFilter extends Filter {

  constructor() {
    super();

    this.id = 0;
    this.internalName = 'all';
    this.name = trans('filters.all');
  }

  updateState(report : OverviewResponse) {
    this.count = report.total;
    this.countDisplay = App.NumberFormatter.abbreviate(this.count);
  }

}

export default AllFilter;
