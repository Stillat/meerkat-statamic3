import BaseResponse from '../baseResponse';
import Type from '../../../Types/type';
import String from '../../../Types/string';

class ErrorReportResponse extends BaseResponse {

  constructor() {
    super();

    this.report = '';
  }

  static fromApiResponse(apiResponse, err) : ErrorReportResponse {
    let newReport = new ErrorReportResponse();

    newReport.success = Type.withDefault(apiResponse[ErrorReportResponse.ApiSuccess], false);
    newReport.report = String.withDefault(apiResponse[ErrorReportResponse.ApiReport], '');

    return newReport;
  }

}

ErrorReportResponse.ApiSuccess = 'success';
ErrorReportResponse.ApiReport = 'report';

export default ErrorReportResponse;
