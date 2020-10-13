import ky from 'ky';
import String from '../Types/string';
import {RequestState} from '../Data/Concerns/canPoolHttpRequests';
import Type from '../Types/type';
import Environment from '../Config/environment';

class Client {

  constructor() {
    this._pendingRequestMapping = {};
    this.api = ky.extend({
      hooks: {
        beforeRequest: [
          request => {
            request.headers.set('X-CSRF-TOKEN', Environment.getCsrfToken());
          }
        ]
      }
    });
  }

  /**
   * Transforms the provide data into a URL-appropriate string.
   *
   * @param {Object} data The data to transform.
   * @returns {string}
   */
  urlEncode(data) {
    let str = [];

    for (let p in data) {
      if (data.hasOwnProperty(p)) {
        str.push(encodeURIComponent(p) + '=' + encodeURIComponent(data[p]));
      }
    }

    return str.join('&');
  }

  /**
   * Invokes all pending request handlers with the provided value.
   *
   * @param {string} request The request hash.
   * @param {string} handlerType The handler method to call.
   * @param {Object|string|number|Error} value The value to supply to the handler.
   * @private
   */
  _processRequestHandlers(request: string, handlerType: string, value: Object) {
    if (Type.hasValue(this._pendingRequestMapping[request]) === false) {
      return;
    }

    if (Type.hasValue(this._pendingRequestMapping[request][handlerType]) === false) {
      return;
    }

    for (let i = 0; i < this._pendingRequestMapping[request][handlerType].length; i += 1) {
      this._pendingRequestMapping[request][handlerType][i](value);
    }

    this._pendingRequestMapping[request].resolveHandlers = [];
    this._pendingRequestMapping[request].rejectHandlers = [];
  }

  /**
   * Issues a GET HTTP request to the provided URL.
   *
   * If multiple requests to the same resource are made in rapid
   * succession, the subsequent requests will be pooled and
   * resolved using the response from the first request.
   *
   * @param {string} url The request URL.
   * @param {Object} data The request data.
   * @param {RequestState} state The request state.
   * @returns {Promise<T>}
   */
  get(url, data, state: RequestState): Promise {
    if (Type.hasValue(this._pendingRequestMapping[state.request]) === false) {
      this._pendingRequestMapping[state.request] = {
        resolveHandlers: [],
        rejectHandlers: []
      };
    }

    if (state.shouldProcess) {
      if (Type.hasValue(data)) {
        url = String.format('{0}?{1}', url, this.urlEncode(data));
      }

      return new Promise(function (resolve, reject) {
        this.api.get(url).then(function (response) {
          let responseJson = response.json();

          resolve(responseJson);

          this._processRequestHandlers(state.request, Client.HandlerResolve, responseJson);
        }.bind(this)).catch(function (err) {
          reject(err);

          this._processRequestHandlers(state.request, Client.HandlerReject, err);
        }.bind(this));
      }.bind(this));
    }

    return new Promise(function (resolve, reject) {
      this._pendingRequestMapping[state.request].resolveHandlers.push(resolve);
      this._pendingRequestMapping[state.request].rejectHandlers.push(reject);
    }.bind(this));
  }

  /**
   * Issues a GET HTTP request to the provided URL.
   *
   * @param {string} url The request URL.
   * @param {Object} data The request data.
   * @returns {Promise<T>}
   */
  getWithoutState(url, data): Promise {
    if (typeof data !== 'undefined' && data !== null) {
      url = String.format('{0}?{1}', url, this.urlEncode(data));
    }

    return new Promise(function (resolve, reject) {
      this.api.get(url).then(function (response) {
        resolve(response.json());
      }).catch(function (err) {
        reject(err);
      });
    });
  }

  _rewritePostData(data) {
    if (Type.hasValue(data)) {
      data = {json: data};
    }

    return data;
  }

  /**
   * Issues a POST HTTP request to the provided URL.
   *
   * If multiple requests to the same resource are made in rapid
   * succession, the subsequent requests will be pooled and
   * resolved using the response from the first request.
   *
   * @param {string} url The request URL.
   * @param {Object} data The request data.
   * @param {RequestState} state The request state.
   * @returns {Promise<T>}
   */
  post(url: string, data: Object, state: RequestState): Promise {
    if (Type.hasValue(this._pendingRequestMapping[state.request]) === false) {
      this._pendingRequestMapping[state.request] = {
        resolveHandlers: [],
        rejectHandlers: []
      };
    }

    if (state.shouldProcess) {
      data = this._rewritePostData(data);

      return new Promise(function (resolve, reject) {
        this.api.post(url, data).then(function (response) {
          let responseJson = response.json();

          resolve(responseJson);

          this._processRequestHandlers(state.request, Client.HandlerResolve, responseJson);
        }.bind(this)).catch(function (err) {
          reject(err);

          this._processRequestHandlers(state.request, Client.HandlerReject, err);
        }.bind(this));
      }.bind(this));
    }

    return new Promise(function (resolve, reject) {
      this._pendingRequestMapping[state.request].resolveHandlers.push(resolve);
      this._pendingRequestMapping[state.request].rejectHandlers.push(reject);
    }.bind(this));
  }

  /**
   * Issues a POST HTTP request to the provided URL.
   *
   * @param {string} url The request URL.
   * @param {Object} data The request data.
   * @returns {Promise<T>}
   */
  postWithoutState(url, data): Promise {
    data = this._rewritePostData(data);

    return new Promise(function (resolve, reject) {
      this.api.post(url, data).then(function (response) {
        resolve(response.json());
      }).catch(function (err) {
        reject(err);
      });
    });
  }

}

Client.HandlerReject = 'rejectHandlers';
Client.HandlerResolve = 'resolveHandlers';

export default Client;
