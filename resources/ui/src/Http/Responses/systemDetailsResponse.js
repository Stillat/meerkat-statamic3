import BaseResponse from './baseResponse';

class SystemDetailsResponse extends BaseResponse {

  constructor() {
    super();

    this.product = null;
    this.version = null;
  }

}

export default SystemDetailsResponse;
