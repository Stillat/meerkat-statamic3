import {registerHubs} from './registerHubs';
import ControlPanelApplication from './controlPanelApplication';
import Bootstrapper from './bootstrapper';
import OverviewProvider from '../Reporting/overviewProvider';

const NumAbbr = require('number-abbreviate');

require('./meerkat.less');

class App {

}

App.NumberFormatter = new NumAbbr();
App.Bootstrapper = Bootstrapper;
App.ControlPanelApplication = ControlPanelApplication;

registerHubs();

OverviewProvider.Instance = new OverviewProvider();
OverviewProvider.Instance.start();

export {
  App
};
