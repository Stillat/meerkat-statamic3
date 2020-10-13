class ThreadContext {

  constructor() {
    this.id = null;
    this.name = '';
    this.createdUtc = 0;
  }

  static fromApiObject(apiObject) : ThreadContext {
    let context = new ThreadContext();

    context.id = apiObject.contextId;
    context.name = apiObject.contextName;
    context.createdUtc = apiObject.createdUtc;

    return context;
  }

}

export default ThreadContext;
