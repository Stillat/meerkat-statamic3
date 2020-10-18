import GroupPermission from './groupPermission';
import ConfigItem from './configItem';

class PermissionsMapper {

  /**
   * Maps the group permissions to the appropriate configuration item locations.
   *
   * @param {Array<ConfigItem>} items The configuration items.
   * @param {Array<GroupPermission>} groupPermissions The group permissions.
   * @returns {*}
   */
  static mapPermissions(items : Array<ConfigItem>, groupPermissions: Array<GroupPermission>) {
    for (let i = 0; i < groupPermissions.length; i++) {
      let currentGroup = groupPermissions[i],
        permissions = currentGroup.permissions;

      if (permissions.hasAllPermissions) {
        items[PermissionsMapper.AllPermissions].value.push(currentGroup.id);
      } else {
        if (permissions.canApproveComments) {
          items[PermissionsMapper.CanApprove].value.push(currentGroup.id);
        }

        if (permissions.canViewComments) {
          items[PermissionsMapper.CanViewComments].value.push(currentGroup.id);
        }

        if (permissions.canEditComments) {
          items[PermissionsMapper.CanEdit].value.push(currentGroup.id);
        }

        if (permissions.canRemoveComments) {
          items[PermissionsMapper.CanRemove].value.push(currentGroup.id);
        }

        if (permissions.canReplyToComments) {
          items[PermissionsMapper.CanReplyToComments].value.push(currentGroup.id);
        }

        if (permissions.canReportAsHam) {
          items[PermissionsMapper.CanReportAsHam].value.push(currentGroup.id);
        }

        if (permissions.canReportAsSpam) {
          items[PermissionsMapper.CanReportAsSpam].value.push(currentGroup.id);
        }

        if (permissions.canUnApproveComments) {
          items[PermissionsMapper.CanUnApproveComments].value.push(currentGroup.id);
        }

        if (permissions.canViewComments) {
          items[PermissionsMapper.CanViewComments].value.push(currentGroup.id);
        }
      }
    }

    return items;
  }

}

PermissionsMapper.AllPermissions = 'permissions.all_permissions';
PermissionsMapper.CanApprove = 'permissions.can_approve_comments';
PermissionsMapper.CanEdit = 'permissions.can_edit_comments';
PermissionsMapper.CanRemove = 'permissions.can_remove_comments';
PermissionsMapper.CanReplyToComments = 'permissions.can_reply_to_comments';
PermissionsMapper.CanReportAsHam = 'permissions.can_report_as_ham';
PermissionsMapper.CanReportAsSpam = 'permissions.can_report_as_spam';
PermissionsMapper.CanUnApproveComments = 'permissions.can_unapprove_comments';
PermissionsMapper.CanViewComments = 'permissions.can_view_comments';

export default PermissionsMapper;
