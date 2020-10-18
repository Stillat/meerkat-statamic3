import Type from '../../Types/type';
import PermissionSet from '../permissionSet';

class GroupPermission {

  constructor() {
    this.id = '';
    this.name = '';
    this.permissions = new PermissionSet();
  }

  static fromApiObject(apiObject) : GroupPermission {
    let groupPerm = new GroupPermission();

    groupPerm.id = Type.withDefault(apiObject[GroupPermission.ApiId], '');
    groupPerm.name = Type.withDefault(apiObject[GroupPermission.ApiName], '');

    if (Type.hasValue(apiObject[GroupPermission.ApiPermissions])) {
      groupPerm.permissions = PermissionSet.fromApiObject(apiObject[GroupPermission.ApiPermissions]);
    }

    return groupPerm;
  }

}

GroupPermission.ApiName = 'name';
GroupPermission.ApiId = 'id';
GroupPermission.ApiPermissions = 'permissions';

export default GroupPermission;

