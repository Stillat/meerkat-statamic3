import BaseResponse from './baseResponse';
import Type from '../../Types/type';

class CommentMutationResponse extends BaseResponse {

  constructor() {
    super();

    this.autoDeleted = false;
    this.comments = [];
    this.comment = null;
  }

  static fromApiResponse(apiResponse, err): CommentMutationResponse {
    let response = new CommentMutationResponse();

    BaseResponse.applyResponseToObject(apiResponse, err, response);

    response.comment = Type.withDefault(apiResponse[CommentMutationResponse.ApiComment], null);
    response.comments = Type.withDefault(apiResponse[CommentMutationResponse.ApiComments], []);
    response.autoDeleted = Type.withDefault(apiResponse[CommentMutationResponse.ApiAutoDeleted], false);

    return response;
  }

}

CommentMutationResponse.ApiComments = 'comments';
CommentMutationResponse.ApiAutoDeleted = 'auto_deleted';
CommentMutationResponse.ApiComment = 'comment';

export default CommentMutationResponse;
