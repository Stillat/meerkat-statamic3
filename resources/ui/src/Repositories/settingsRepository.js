import {canPoolHttpRequests} from '../Data/Concerns/canPoolHttpRequests';
import Client from '../Http/client';
import SettingsResponse from '../Http/Responses/settingsResponse';
import ErrorResponse from '../Http/Responses/errorResponse';
import {hash} from '../Data/Concerns/canBeStringOrHash';
import Endpoints from '../Http/endpoints';
import ActionState from '../App/actionState';
import SaveSettingsResponse from '../Http/Responses/saveSettingsResponse';
import ValidateAkismetResponse from '../Http/Responses/validateAkismetResponse';
import ChangeSetResponse from '../Http/Responses/changeSetResponse';
import BaseResponse from '../Http/Responses/baseResponse';

/**
 * Provides a wrapper around Meerkat's configuration-related HTTP API endpoints.
 *
 * @property {function(request) : RequestState} shouldProcessRequest()
 * @property {function(request)} releasePending()
 */
class SettingsRepository {

  constructor() {
    canPoolHttpRequests(this);
    this.client = new Client();
  }

  updatePerPage(perPage) :Promise<BaseResponse | ErrorResponse> {
    let request = {'per_page': perPage},
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.SettingsUpdatePerPage), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);
          resolve(BaseResponse.fromApiResponse(result, null));
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  getCurrentChangeSet(): Promise<ChangeSetResponse | ErrorResponse> {
    let request = {},
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.get(Endpoints.url(Endpoints.SettingsGetCurrentChangeSet), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);
          resolve(ChangeSetResponse.fromApiObject(result, null));
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  validateAkismet(apiKey, frontPage): Promise<ValidateAkismetResponse | ErrorResponse> {
    let request = {
        'api_key': apiKey,
        'front_page': frontPage,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.SettingsValidateAkismet), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);
          resolve(ValidateAkismetResponse.fromApiObject(result, null));
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  saveSettings(settings): Promise<SaveSettingsResponse | ErrorResponse> {
    let request = {
        settings: settings,
        actionId: ActionState.CurrentActionId
      },
      requestHash = hash(request);

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.post(Endpoints.url(Endpoints.SettingsSave), request, requestState)
        .then(function (result) {
          this.releasePending(requestHash);
          resolve(SaveSettingsResponse.fromApiObject(result, null));
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

  getSettings(): Promise<SettingsResponse | ErrorResponse> {
    let requestHash = hash({});

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 500);

      this.client.get(Endpoints.url(Endpoints.SettingsFetch), {}, requestState)
        .then(function (result) {
          this.releasePending(requestHash);
          resolve(SettingsResponse.fromApiResponse(result, null));
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }

}

SettingsRepository.Instance = new SettingsRepository();

export default SettingsRepository;
