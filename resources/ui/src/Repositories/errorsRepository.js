import ErrorResponse from '../Http/Responses/errorResponse';
import Client from '../Http/client';
import {canPoolHttpRequests} from '../Data/Concerns/canPoolHttpRequests';
import ErrorReportResponse from '../Http/Responses/Logging/errorReportResponse';
import {hash} from '../Data/Concerns/canBeStringOrHash';
import Endpoints from '../Http/endpoints';
import BaseResponse from '../Http/Responses/baseResponse';

/**
 * @property {function(request, waitTime) : RequestState} shouldProcessRequest()
 * @property {function(request)} releasePending()
 */
class ErrorsRepository {

  constructor() {
    canPoolHttpRequests(this);
    this.client = new Client();
  }

  submitActionReport(actionId: String) : Promise<BaseResponse> {
    let request = {
        action: actionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.TelemetrySubmitReport), request, requestState)
        .then(function (result) {
          resolve(BaseResponse.fromApiResponse(result));
          this.releasePending(requestHash);
        }.bind(this))
        .catch(function (err) {
          reject(ErrorResponse.fromError(err));
          this.releasePending(requestHash);
        }.bind(this));
    }.bind(this));
  }

  getReportLog(actionId: String): Promise<ErrorReportResponse> {
    let request = {
        action: actionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.get(Endpoints.url(Endpoints.TelemetryViewReport), request, requestState)
        .then(function (result) {
          resolve(ErrorReportResponse.fromApiResponse(result));
          this.releasePending(requestHash);
        }.bind(this))
        .catch(function (err) {
          reject(ErrorResponse.fromError(err));
          this.releasePending(requestHash);
        }.bind(this));
    }.bind(this));
  }

}

ErrorsRepository.Instance = new ErrorsRepository();

export default ErrorsRepository;
