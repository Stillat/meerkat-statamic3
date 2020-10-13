import BaseResponse from './baseResponse';

class PagedResponse extends BaseResponse {

  constructor() {
    super();

    this.totalResults = 0;
    this.currentPage = 1;
    this.hasNextPage = false;
    this.hasPreviousPage = false;
  }

}

export default PagedResponse;
