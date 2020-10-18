import BaseResponse from './baseResponse';
import Type from '../../Types/type';

class ChangeSetResponse extends BaseResponse {

  constructor() {
    super();

    this.changeSet = '';
  }

  static fromApiObject(apiObject, err): ChangeSetResponse {
    let response = new ChangeSetResponse();

    BaseResponse.applyResponseToObject(apiObject, err, response);

    response.changeSet = Type.withDefault(apiObject[ChangeSetResponse.ApiChangeSet], '');

    return response;
  }

}

ChangeSetResponse.ApiChangeSet = 'change_set';

export default ChangeSetResponse;
