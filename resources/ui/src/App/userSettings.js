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

  getDisplayTableFilters(): Boolean {
    let settings = this.getSettings();

    return Type.withDefault(settings[UserSettings.SettingDisplayTableFilter], false);
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
      displayTableFilter: false
    };
  }

}

UserSettings.SettingDisplayTableFilter = 'displayTableFilter';
UserSettings.SettingsKey = 'meerkat_user_preferences';

export default UserSettings;
