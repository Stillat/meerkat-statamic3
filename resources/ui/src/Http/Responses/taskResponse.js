import BaseResponse from './baseResponse';
import TaskStatus from '../../Data/taskStatus';
import Type from '../../Types/type';

class TaskResponse extends BaseResponse {

  constructor() {
    super();

    this.taskId = null;
    this.status = TaskStatus.InProgress;
  }

  static fromApiResponse(apiResponse, err) : TaskResponse {
    let response = new TaskResponse();

    BaseResponse.applyResponseToObject(apiResponse, err, response);

    response.taskId = Type.withDefault(apiResponse[TaskResponse.ApiTaskId], null);
    response.status = Type.withDefault(apiResponse[TaskResponse.ApiTaskStatus], TaskStatus.InProgress);

    return response;
  }

}

TaskResponse.ApiTaskId = 'task';
TaskResponse.ApiTaskStatus = 'status';

export default TaskResponse;
