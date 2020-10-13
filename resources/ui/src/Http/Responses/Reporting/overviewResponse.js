import BaseResponse from '../baseResponse';
import Type from '../../../Types/type';

class OverviewResponse extends BaseResponse {

  constructor() {
    super();

    this.total = 0;
    this.totalThreads = 0;
    this.needsMigration = 0;
    this.totalSpam = 0;
    this.totalHam = 0;
    this.requiresReview = 0;
    this.totalPublished = 0;
    this.publishedAndSpam = 0;
    this.pending = 0;
    this.completionTime = 0;
  }

  static fromApiResponse(apiResponse, err): OverviewResponse {
    let response = new OverviewResponse();

    BaseResponse.applyResponseToObject(apiResponse, err, response);

    response.total = Type.withDefault(apiResponse[OverviewResponse.ApiTotal], 0);
    response.totalThreads = Type.withDefault(apiResponse[OverviewResponse.ApiTotalThreads], 0);
    response.needsMigration = Type.withDefault(apiResponse[OverviewResponse.ApiNeedsMigration], 0);
    response.totalSpam = Type.withDefault(apiResponse[OverviewResponse.ApiIsSpam], 0);
    response.totalHam = Type.withDefault(apiResponse[OverviewResponse.ApiIsHam], 0);
    response.requiresReview = Type.withDefault(apiResponse[OverviewResponse.ApiRequiresReview], 0);
    response.totalPublished = Type.withDefault(apiResponse[OverviewResponse.ApiIsPublished], 0);
    response.publishedAndSpam = Type.withDefault(apiResponse[OverviewResponse.ApiPublishedAndSpam], 0);
    response.pending = Type.withDefault(apiResponse[OverviewResponse.ApiPending], 0);
    response.completionTime = Type.withDefault(apiResponse[OverviewResponse.ApiCompletionTime], 0);

    return response;
  }

}

OverviewResponse.ApiTotal = 'total';
OverviewResponse.ApiTotalThreads = 'total_threads';
OverviewResponse.ApiNeedsMigration = 'needs_migration';
OverviewResponse.ApiIsSpam = 'is_spam';
OverviewResponse.ApiIsHam = 'is_ham';
OverviewResponse.ApiRequiresReview = 'requires_review';
OverviewResponse.ApiIsPublished = 'is_published';
OverviewResponse.ApiPublishedAndSpam = 'published_and_spam';
OverviewResponse.ApiPending = 'pending';
OverviewResponse.ApiCompletionTime = 'completion_time';

export default OverviewResponse;
