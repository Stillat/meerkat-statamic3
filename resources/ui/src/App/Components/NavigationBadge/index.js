import template from './template.html';
import OverviewProvider from '../../../Reporting/overviewProvider';
import OverviewResponse from '../../../Http/Responses/Reporting/overviewResponse';
import {App} from '../../index';

export default {
  template: template,
  data() {
    return {
      shouldDisplay: false,
      count: 0,
      countDisplay: ''
    };
  },
  methods: {
    updateDisplay(report: OverviewResponse) {
      if (report.success) {
        this.count = report.pending;

        if (this.count > 0) {
          this.countDisplay = App.NumberFormatter.abbreviate(this.count, 2);
          this.shouldDisplay = true;
        } else {
          this.shouldDisplay = false;
        }
      }
    }
  },
  created() {
    OverviewProvider.Instance.on('updated', this.updateDisplay.bind(this));
  }
};
