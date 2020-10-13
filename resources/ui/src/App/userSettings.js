import Type from '../Types/type';

const store = require('store');

class UserSettings {

  getSettings() {
    let userPreferences = store.get(UserSettings.SettingsKey);

    if (Type.hasValue(userPreferences) === false) {
      userPreferences = this._getDefaultSettings();

      this._saveSettings(userPreferences);
    }

    return userPreferences;
  }

  getPerPage(): Number {
    return this.getSettings().perPage;
  }

  getDisplayTableFilters(): Boolean {
    let settings = this.getSettings();

    return Type.withDefault(settings[UserSettings.SettingDisplayTableFilter], false);
  }

  updatePerPage(perPage: Number) {
    let preferences = this.getSettings();

    preferences.perPage = perPage;

    this._saveSettings(preferences);
  }

  updateDisplayTableFilters(display: Boolean) {
    let preferences = this.getSettings();

    preferences.displayTableFilter = display;

    this._saveSettings(preferences);
  }

  _saveSettings(settings: Object) {
    store.set(UserSettings.SettingsKey, settings);
  }

  _getDefaultSettings() {
    return {
      perPage: 10,
      displayTableFilter: false
    };
  }

}

UserSettings.SettingDisplayTableFilter = 'displayTableFilter';
UserSettings.SettingsKey = 'meerkat_user_preferences';

export default UserSettings;
