import Type from '../Types/type';

class PermissionSet {

  constructor() {
    this.hasAllPermissions = false;
    this.canApproveComments = false;
    this.canEditComments = false;
    this.canRemoveComments = false;
    this.canReplyToComments = false;
    this.canReportAsHam = false;
    this.canReportAsSpam = false;
    this.canUnApproveComments = false;
    this.canViewComments = false;
  }

  static fromApiObject(apiObject): PermissionSet {
    let permissionSet = new PermissionSet();

    permissionSet.hasAllPermissions = Type.withDefault(apiObject[PermissionSet.ApiAllPermissions], false);
    permissionSet.canApproveComments = Type.withDefault(apiObject[PermissionSet.ApiCanApproveComments], false);
    permissionSet.canEditComments = Type.withDefault(apiObject[PermissionSet.ApiCanEditComments], false);
    permissionSet.canRemoveComments = Type.withDefault(apiObject[PermissionSet.ApiCanRemoveComments], false);
    permissionSet.canReplyToComments = Type.withDefault(apiObject[PermissionSet.ApiCanReplyToComments], false);
    permissionSet.canReportAsHam = Type.withDefault(apiObject[PermissionSet.ApiCanReportAsHam], false);
    permissionSet.canReportAsSpam = Type.withDefault(apiObject[PermissionSet.ApiCanReportAsSpam], false);
    permissionSet.canUnApproveComments = Type.withDefault(apiObject[PermissionSet.ApiCanUnapproveComments], false);
    permissionSet.canViewComments = Type.withDefault(apiObject[PermissionSet.ApiCanViewComments], false);

    return permissionSet;
  }

}

PermissionSet.ApiAllPermissions = 'all_permissions';
PermissionSet.ApiCanApproveComments = 'can_approve_comments';
PermissionSet.ApiCanEditComments = 'can_edit_comments';
PermissionSet.ApiCanRemoveComments = 'can_remove_comments';
PermissionSet.ApiCanReplyToComments = 'can_reply_to_comments';
PermissionSet.ApiCanReportAsHam = 'can_report_as_ham';
PermissionSet.ApiCanReportAsSpam = 'can_report_as_spam';
PermissionSet.ApiCanUnapproveComments = 'can_unapprove_comments';
PermissionSet.ApiCanViewComments = 'can_view_comments';

export default PermissionSet;
