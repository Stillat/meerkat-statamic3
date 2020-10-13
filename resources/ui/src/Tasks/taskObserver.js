import EventEmitter from 'wolfy87-eventemitter';
import TasksRepository from '../Repositories/tasksRepository';
import TaskStatus from '../Data/taskStatus';

class TaskObserver extends EventEmitter {

  constructor() {
    super();

    this.tasks = new TasksRepository();
    this.intervalId = null;
    this.taskId = null;
  }

  /**
   * Watches the specified tasks status for server-side changes.
   *
   * @param {string} taskId The task identifier.
   */
  watch(taskId) {
    this.taskId = taskId;

    this.intervalId = window.setInterval(function () {
      this.tasks.getStatus(this.taskId).then(function (response) {
        if (response.success === true) {
          if (response.status === TaskStatus.Complete) {
            window.clearInterval(this.intervalId);
            this.emit(TaskObserver.EventComplete);
          } else if (response.status === TaskStatus.Canceled) {
            this.emit(TaskObserver.EventCanceled);
            window.clearInterval(this.intervalId);
          }
        }
      }.bind(this))
        .catch(function (err) {
          window.clearInterval(this.intervalId);
          this.emitEvent(TaskObserver.EventError, err);
        }.bind(this));
    }.bind(this), 1000);
  }

  /**
   * Ensures that the interval has been cleared.
   */
  ensureStopped() {
    if (this.intervalId !== null) {
      window.clearInterval(this.intervalId);
    }
  }

}

TaskObserver.EventError = 'error';
TaskObserver.EventComplete = 'complete';
TaskObserver.EventCanceled = 'canceled';

export default TaskObserver;
