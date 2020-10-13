import EventEmitter from 'wolfy87-eventemitter';
import ReportingRepository from '../Repositories/reportingRepository';

class OverviewProvider extends EventEmitter {

  constructor() {
    super();

    this.intervalId = null;
    this.report = null;
    this.lastError = null;
  }

  _updateNow() {
    /* We will use the Instance property so that we can share pooled requests app-wide. */
    ReportingRepository.Instance.overview().then(function (report) {
      this.report = report;

      this.emit(OverviewProvider.EventUpdated, this.report);
    }.bind(this)).catch(function (err) {
      this.lastError = err;

      this.emit(OverviewProvider.EventError, this.lastError);
    }.bind(this));
  }

  hasData() {
    return this.report !== null;
  }

  start() {
    this._updateNow();
    window.setInterval(this._updateNow.bind(this), 30000);
  }

  stop() {
    if (this.intervalId !== null) {
      window.clearInterval(this.intervalId);
      this.intervalId = null;
    }
  }

  refresh() {
    this._updateNow();
  }

}

OverviewProvider.EventError = 'error';
OverviewProvider.EventUpdated = 'updated';
OverviewProvider.Instance = null;

export default OverviewProvider;
