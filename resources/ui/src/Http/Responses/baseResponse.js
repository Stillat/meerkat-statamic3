import Type from '../../Types/type';
import String from '../../Types/string';

class BaseResponse {

  constructor() {
    this.success = true;
    this.isRecoverable = false;
    this.errorCode = null;
    this.msg = '';
    this.error = null;
    this.authorized = true;
    this.permission = null;
  }

  static fromApiResponse(apiResponse, err): BaseResponse {
    let response = new BaseResponse();

    BaseResponse.applyResponseToObject(apiResponse, err, response);

    return response;
  }

  static applyResponseToObject(apiResponse, err, object) {
    object.success = Type.withDefault(apiResponse[BaseResponse.ApiSuccess], false);
    object.isRecoverable = Type.withDefault(apiResponse[BaseResponse.ApiIsRecoverable], true);
    object.errorCode = Type.withDefault(apiResponse[BaseResponse.ApiErrorCode], null);
    object.msg = String.withDefault(apiResponse[BaseResponse.ApiMessage], '');
    object.error = Type.withDefault(err, null);
  }

}

BaseResponse.ApiSuccess = 'success';
BaseResponse.ApiIsRecoverable = 'is_recoverable';
BaseResponse.ApiMessage = 'msg';
BaseResponse.ApiErrorCode = 'error_code';

export default BaseResponse;
