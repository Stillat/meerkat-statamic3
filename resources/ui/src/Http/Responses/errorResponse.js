import BaseResponse from './baseResponse';
import Type from '../../Types/type';

class ErrorResponse extends BaseResponse {

  static fromError(err) {
    let response = new ErrorResponse();

    response.success = false;
    response.error = err;

    if (Type.hasValue(err, 'response')) {
      if (Type.hasValue(err.response, 'status')) {
        if (err.response.status === 403) {
          response.authorized = false;
          response.permission = err.response.headers.get('meerkat-permission');
        }
      }
    }

    return response;
  }

  static makeStateError() {
    let response = new ErrorResponse();

    response.success = false;
    response.err = null;

    return response;
  }

  getMessage(): string {
    return Type.typeOf(this.error) + '\n' + this.error.message + '\n' + this.error.stack;
  }

}

export default ErrorResponse;
