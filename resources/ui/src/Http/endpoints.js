import Environment from '../Config/environment';
import String from '../Types/string';

class Endpoints {

  static url(endpoint) {
    let baseUrl = String.finish(Environment.StatamicApiRoot, '/');

    return baseUrl + Endpoints.Prefix + endpoint;
  }

}

Endpoints.Prefix = 'api/meerkat/';
Endpoints.SystemDetails = '';
Endpoints.CommentsSearch = 'comments/search';
Endpoints.CommentsApprove = 'comments/publish';
Endpoints.CommentsApproveMany = 'comments/publish-many';
Endpoints.CommentsReply = 'comments/reply';
Endpoints.CommentsUnapprove = 'comments/unpublish';
Endpoints.CommentsUnapproveMany = 'comments/unpublish-many';
Endpoints.CommentsRemove = 'comments/remove';
Endpoints.CommentsRemoveMany = 'comments/remove-many';
Endpoints.CommentsRemoveSpam = 'comments/remove-all-spam';
Endpoints.CommentMarkSpam = 'comments/report-spam';
Endpoints.CommentMarkManySpam = 'comments/report-many-spam';
Endpoints.CommentMarkHam = 'comments/report-ham';
Endpoints.CommentMarkManyHam = 'comments/report-many-ham';
Endpoints.CommentsUpdate = 'comments/update';
Endpoints.CommentsCheckForSpam = 'comments/check-for-spam';

Endpoints.TaskGetStatus = 'tasks/status';

Endpoints.SettingsFetch = 'settings/fetch';
Endpoints.SettingsSave = 'settings/save';
Endpoints.SettingsGetCurrentChangeSet = 'settings/current-change-set';
Endpoints.SettingsValidateAkismet = 'settings/validate-akismet';
Endpoints.SettingsUpdatePerPage = 'settings/update-per-page';

Endpoints.ExportCsv = 'export/csv';
Endpoints.ExportJson = 'export/json';

Endpoints.ReportingOverview = 'reporting/overview';

Endpoints.TelemetryViewReport = 'telemetry/report';
Endpoints.TelemetrySubmitReport = 'telemetry/submit';

export default Endpoints;
