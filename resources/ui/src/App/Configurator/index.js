import template from './template.html';
import UsesTranslator from '../Mixins/usesTranslator';
import Publishing from './Panels/Publishing';
import Guard from './Panels/Guard';
import IpFilter from './Panels/IpFilter';
import Permissions from './Panels/Permissions';
import WordFilter from './Panels/WordFilter';
import Privacy from './Panels/Privacy';
import Email from './Panels/Email';
import SettingsRepository from '../../Repositories/settingsRepository';
import ControlPanelApplication from '../controlPanelApplication';
import String from '../../Types/string';
import Type from '../../Types/type';
import Environment from '../../Config/environment';
import AvatarDriverRegistry from '../../Extend/Avatars/avatarDriverRegistry';
import PermissionsMapper from '../../Data/Configuration/permissionsMapper';
import Loader from '../Components/Loader';
import GuardMapper from '../../Data/Configuration/guardMapper';

require('./style.less');
const syncjs = require('syncjs');

export default {
  mixins: [UsesTranslator],
  template: template,
  components: {
    'publishing': Publishing,
    'guard': Guard,
    'ip-filter': IpFilter,
    'permissions': Permissions,
    'word-filter': WordFilter,
    'privacy': Privacy,
    'email': Email,
    'loader': Loader
  },
  data() {
    return {
      activePage: 'publishing',
      settings: null,
      lastError: null,
      wordFilterEnabled: false,
      ipFilterEnabled: false,
      userEmail: 'example@example.org',
      akismetFilterEnabled: false,
      avatarOptions: [],
      canChangeConfig: false,
      hasLoaded: false,
      perPageOptions: [
        10,
        25,
        50,
        100
      ],
      userPreferences: {
        avatarDriver: 'initials',
        perPage: 25
      },
      state: {
        serverDiffers: false,
        loading: true,
        lastError: null
      }
    };
  },
  watch: {
    'settings.guards': function (newVal) {
      this.updateGuardConfiguration();
    }
  },
  methods: {
    onConfigUserAvailable(config) {
      this.reloadConfigState();
    },
    reloadConfigState() {
      if (Environment.isControlPanelConfigEnabled()) {
        if (Environment.UserPreferences.isSuper === true) {
          this.watchForServerConfigChanges();
          this.canChangeConfig = true;
        }
      }

      if (Type.hasValue(Environment.UserPreferences)) {
        this.userPreferences.avatarDriver = Environment.UserPreferences.cp_avatar_driver;
        this.userPreferences.perPage = Environment.UserPreferences.cp_per_page;
        this.userEmail = Environment.UserPreferences.email;
      }

      this.reloadSettings();
    },
    watchForServerConfigChanges() {
      window.setInterval(function () {
        SettingsRepository.Instance.getCurrentChangeSet().then(function (response) {
          this.state.serverDiffers = response.changeSet !== this.settings.changeSet;
        }.bind(this));
      }.bind(this), 5000);
    },
    refreshAvatarDrivers() {
      let currentDriverMapping = AvatarDriverRegistry.DriverMapping,
        newOptions = [];

      for (let prop in currentDriverMapping) {
        newOptions.push({
          value: prop,
          driverName: currentDriverMapping[prop]
        });
      }

      newOptions.sort(function (a, b) {
        let aName = a.driverName,
          bName = b.driverName;

        return (aName < bName) ? -1 : (aName > bName) ? 1 : 0;
      });

      this.avatarOptions = newOptions;
    },
    updateGuardConfiguration() {
      this.wordFilterEnabled = this.hasGuardEnabled('WordFilterSpamGuard');
      this.ipFilterEnabled = this.hasGuardEnabled('IpFilterSpamGuard');
      this.akismetFilterEnabled = this.hasGuardEnabled('AkismetSpamGuard');
    },
    hasGuardEnabled(relativeClassName) {
      if (this.settings === null) {
        return false;
      }

      for (let i = 0; i < this.settings.guards.length; i++) {
        if (String.endsWith(this.settings.guards[i].class, '\\' + relativeClassName)) {
          return this.settings.guards[i].enabled;
        }
      }

      return false;
    },
    getSettings() {
      let returnItems = {};

      for (let prop in this.settings.items) {
        if (this.settings.items.hasOwnProperty(prop)) {
          let curProp = this.settings.items[prop];

          curProp.defaults = null;
          returnItems[prop] = curProp;
        }
      }

      returnItems[GuardMapper.SpamGuards].value = [];
      returnItems[PermissionsMapper.AllPermissions].value = [];
      returnItems[PermissionsMapper.CanApprove].value = [];
      returnItems[PermissionsMapper.CanEdit].value = [];
      returnItems[PermissionsMapper.CanEdit].value = [];
      returnItems[PermissionsMapper.CanReplyToComments].value = [];
      returnItems[PermissionsMapper.CanReportAsSpam].value = [];
      returnItems[PermissionsMapper.CanReportAsHam].value = [];
      returnItems[PermissionsMapper.CanUnApproveComments].value = [];
      returnItems[PermissionsMapper.CanViewComments].value = [];

      returnItems = GuardMapper.mapGuards(returnItems, this.settings.guards);
      returnItems = PermissionsMapper.mapPermissions(returnItems, this.settings.permissions);

      return {
        items: returnItems,
        user: {
          perPage: this.userPreferences.perPage,
          avatar: this.userPreferences.avatarDriver
        }
      };
    },
    saveSettings() {
      SettingsRepository.Instance.saveSettings(this.getSettings()).then(function (response) {
        if (response.success) {
          syncjs.Hubs.config().avatarUpdated([this.userPreferences.avatarDriver]);

          ControlPanelApplication.current().controlPanel.message().success(
            this.trans('config.updated')
          );
        } else {
          if (response.settingsUpdated === false && response.preferencesUpdated === false) {
            ControlPanelApplication.current().controlPanel.message().error(
              this.trans('errors.config_both_failure')
            );
          } else {
            if (response.settingsUpdated === false) {
              ControlPanelApplication.current().controlPanel.message().error(
                this.trans('errors.config_settings_failure')
              );
            } else {
              ControlPanelApplication.current().controlPanel.message().error(
                this.trans('errors.config_preferences_failure')
              );
            }
          }
        }
        this.reloadSettings();
      }.bind(this)).catch(function (err) {
        this.state.lastError = err;
        ControlPanelApplication.current().controlPanel.message().error(
          this.trans('errors.const_preferences_unknown_failure')
        );
      }.bind(this));
    },
    reloadSettings() {
      this.state.loading = true;

      SettingsRepository.Instance.getSettings().then(function (settings) {
        this.settings = settings;
        this.state.loading = false;
        this.hasLoaded = true;
        this.state.serverDiffers = false;
      }.bind(this)).catch(function (err) {
        this.lastError = err;
        this.state.loading = false;
      }.bind(this));
    }
  },
  created() {
    syncjs.Hubs.config().handledBy(this);
    this.refreshAvatarDrivers();
    this.reloadConfigState();
  }

};
