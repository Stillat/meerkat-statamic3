import BaseResponse from './baseResponse';
import Type from '../../Types/type';

class UserConfigurationResponse extends BaseResponse {

  constructor() {
    super();

    this.settings = null;
  }

  static fromApiResponse(apiResponse, err): UserConfigurationResponse {
    let response = new UserConfigurationResponse();

    BaseResponse.applyResponseToObject(apiResponse, err, response);
    response.settings = Type.withDefault(apiResponse[UserConfigurationResponse.ApiSettings], null);

    return response;
  }

}

UserConfigurationResponse.ApiSettings = 'settings';

export default UserConfigurationResponse;

