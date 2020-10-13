import BaseResponse from './baseResponse';
import Type from '../../Types/type';

class AffectedCommentsResponse extends BaseResponse {

  constructor() {
    super();

    this.partialSuccess = false;
    this.comments = [];
  }

  static fromApiResponse(apiResponse, err): AffectedCommentsResponse {
    let response = new AffectedCommentsResponse();

    BaseResponse.applyResponseToObject(apiResponse, err, response);

    response.comments = Type.withDefault(apiResponse[AffectedCommentsResponse.ApiComments], []);

    if (response.success === false && response.comments.length > 0) {
      response.partialSuccess = true;
    }

    return response;
  }

}

AffectedCommentsResponse.ApiComments = 'comments';

export default AffectedCommentsResponse;
