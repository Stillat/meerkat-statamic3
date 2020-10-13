import Type from '../../Types/type';

/**
 * @property {function(request, waitTime) : RequestState} shouldProcessRequest()
 * @property {function(request)} releasePending()
 */

export class RequestState {
  constructor() {
    this.shouldProcess = false;
    this.shouldWait = false;
    this.pendingRequests = 0;
    this.request = '';
  }
}

export function canPoolHttpRequests(instance) {
  instance._requestedOnMapping = {};

  instance.releasePending = function (request) {
    this._requestedOnMapping[request].isPending = false;
  }.bind(instance);

  instance.shouldProcessRequest = function (request, waitTime): RequestState {
    let state = new RequestState();

    state.request = request;

    if (Type.hasValue(this._requestedOnMapping[request]) === false ||
      this._requestedOnMapping[request].isPending === false) {
      this._requestedOnMapping[request] = {
        isPending: true,
        pendingRequests: 1
      };

      state.pendingRequests = 1;
      state.shouldProcess = true;
      state.shouldWait = false;

      return state;
    }

    if (this._requestedOnMapping[request].isPending) {
      this._requestedOnMapping[request].pendingRequests += 1;
      state.shouldProcess = false;
      state.shouldWait = true;
      state.pendingRequests = this._requestedOnMapping[request].pendingRequests;

      return state;
    }

    state.shouldProcess = true;
    state.shouldWait = false;
    this._requestedOnMapping[request].pendingRequests = 1;
    state.pendingRequests = this._requestedOnMapping[request].pendingRequests;

    return state;
  }.bind(instance);
}
