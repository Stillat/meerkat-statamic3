import Client from '../Http/client';
import {canPoolHttpRequests} from '../Data/Concerns/canPoolHttpRequests';
import OverviewResponse from '../Http/Responses/Reporting/overviewResponse';
import {hash} from '../Data/Concerns/canBeStringOrHash';
import Endpoints from '../Http/endpoints';
import ErrorResponse from '../Http/Responses/errorResponse';

/**
 * @property {function(request, waitTime) : RequestState} shouldProcessRequest()
 * @property {function(request)} releasePending()
 */
class ReportingRepository {

  constructor() {
    canPoolHttpRequests(this);
    this.client = new Client();
  }

  overview(): Promise<OverviewResponse> {
    let requestHash = hash({});

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 1000);

      this.client.get(Endpoints.url(Endpoints.ReportingOverview), {}, requestState)
        .then(function (result) {
          this.releasePending(requestHash);
          resolve(OverviewResponse.fromApiResponse(result, null));
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

}

ReportingRepository.Instance = new ReportingRepository();

export default ReportingRepository;

