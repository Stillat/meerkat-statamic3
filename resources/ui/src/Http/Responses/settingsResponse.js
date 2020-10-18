import BaseResponse from './baseResponse';
import Type from '../../Types/type';
import ConfigItem from '../../Data/Configuration/configItem';
import SpamGuard from '../../Data/Configuration/spamGuard';
import GroupPermission from '../../Data/Configuration/groupPermission';

class SettingsResponse extends BaseResponse {

  constructor() {
    super();

    this.hasManagedItems = true;
    this.changeSet = '';
    this.items = {};
    this.permissions = [];
    this.guards = [];
  }

  static fromApiResponse(apiResponse, err): SettingsResponse {
    let response = new SettingsResponse();

    BaseResponse.applyResponseToObject(apiResponse, err, response);
    response.hasManagedItems = Type.withDefault(apiResponse[SettingsResponse.ApiHasManaged], true);
    response.changeSet = Type.withDefault(apiResponse[SettingsResponse.ApiCurrentChangeSet], '');

    let configItems = Type.withDefault(apiResponse[SettingsResponse.ApiConfig], []),
      permissions = Type.withDefault(apiResponse[SettingsResponse.ApiPermissions], []),
      guards = Type.withDefault(apiResponse[SettingsResponse.ApiSpamGuards], []);

    for (let i = 0; i < configItems.length; i++) {
      let newItem = ConfigItem.fromApiObject(configItems[i]);

      response.items[newItem.runtimeValue] = newItem;
    }

    for (let i = 0; i < guards.length; i++) {
      let newGuardItem = SpamGuard.fromApiObject(guards[i]);

      response.guards.push(newGuardItem);
    }

    for (let i = 0; i < permissions.length; i++) {
      let newPermItem = GroupPermission.fromApiObject(permissions[i]);

      response.permissions.push(newPermItem);
    }

    return response;
  }

}

SettingsResponse.ApiConfig = 'config';
SettingsResponse.ApiHasManaged = 'has_managed';
SettingsResponse.ApiSpamGuards = 'spam_guards';
SettingsResponse.ApiCurrentChangeSet = 'change_set';
SettingsResponse.ApiPermissions = 'permissions';

export default SettingsResponse;

