import Endpoints from '../Http/endpoints';
import Client from '../Http/client';
import ErrorResponse from '../Http/Responses/errorResponse';
import {canPoolHttpRequests} from '../Data/Concerns/canPoolHttpRequests';
import {hash} from '../Data/Concerns/canBeStringOrHash';
import TaskResponse from '../Http/Responses/taskResponse';

/**
 * @property {function(request, waitTime) : RequestState} shouldProcessRequest()
 * @property {function(request)} releasePending()
 */
class TasksRepository {

  constructor() {
    canPoolHttpRequests(this);
    this.client = new Client();
  }

  /**
   * Retrieves the tasks's current status.
   *
   * @param {string} taskId The task's identifier.
   * @returns {Promise<TaskResponse | ErrorResponse>}
   */
  getStatus(taskId : string) : Promise<TaskResponse | ErrorResponse> {
    let requestHash = hash({
      task: taskId
    });

    return new Promise(function (resolve, reject) {
      let requestState = this.shouldProcessRequest(requestHash, 1000);

      this.client.get(Endpoints.url(Endpoints.TaskGetStatus), {
        task: taskId
      }, requestState)
        .then(function (result) {
          this.releasePending(requestHash);
          resolve(TaskResponse.fromApiResponse(result, null));
        }.bind(this))
        .catch(function (err) {
          this.releasePending(requestHash);
          reject(ErrorResponse.fromError(err));
        }.bind(this));
    }.bind(this));
  }
}

export default TasksRepository;
