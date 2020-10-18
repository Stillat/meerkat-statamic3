import BaseResponse from './baseResponse';
import Type from '../../Types/type';

class ValidateAkismetResponse extends BaseResponse {

  constructor() {
    super();

    this.message = '';
  }

  static fromApiObject(apiObject, err): ValidateAkismetResponse {
    let response = new ValidateAkismetResponse();

    BaseResponse.applyResponseToObject(apiObject, err, response);

    response.message = Type.withDefault(apiObject[ValidateAkismetResponse.ApiMessage], '');

    return response;
  }

}

ValidateAkismetResponse.ApiMessage = 'message';

export default ValidateAkismetResponse;
