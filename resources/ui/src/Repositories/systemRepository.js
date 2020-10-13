import Endpoints from '../Http/endpoints';
import Client from '../Http/client';
import SystemDetailsResponse from '../Http/Responses/systemDetailsResponse';
import deepmerge from 'deepmerge';
import ErrorResponse from '../Http/Responses/errorResponse';

/**
 * Provides methods for accessing Meerkat product and system details.
 */
class SystemRepository {

  constructor() {
    this.client = new Client();
  }

  /**
   * Fetches the current Meerkat product details.
   *
   * @returns {Promise<SystemDetailsResponse>}
   */
  getDetails() :Promise<SystemDetailsResponse> {
    return new Promise(function (resolve, reject) {
      this.client.get(Endpoints.url(Endpoints.SystemDetails)).then(function (result) {
        let response = deepmerge(new SystemDetailsResponse(), result);

        resolve(response);
      }).catch(function (e) {
        reject(ErrorResponse.fromError(e));
      });
    }.bind(this));
  }

}

export default SystemRepository;
