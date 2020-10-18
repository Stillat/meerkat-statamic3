import BaseResponse from './baseResponse';
import Type from '../../Types/type';

class SaveSettingsResponse extends BaseResponse {

  constructor() {
    super();

    this.preferencesUpdated = false;
    this.settingsUpdated = false;
  }

  static fromApiObject(apiObject, err): SaveSettingsResponse {
    let response = new SaveSettingsResponse();

    BaseResponse.applyResponseToObject(apiObject, err, response);

    response.preferencesUpdated = Type.withDefault(apiObject[SaveSettingsResponse.ApiPreferencesUpdated], false);
    response.settingsUpdated = Type.withDefault(apiObject[SaveSettingsResponse.ApiSettingsUpdated], false);

    return response;
  }

}

SaveSettingsResponse.ApiPreferencesUpdated = 'preferences_updated';
SaveSettingsResponse.ApiSettingsUpdated = 'settings_updated';

export default SaveSettingsResponse;
