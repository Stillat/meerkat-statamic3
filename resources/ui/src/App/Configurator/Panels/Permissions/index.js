import template from './template.html';
import SettingsResponse from '../../../../Http/Responses/settingsResponse';
import UsesTranslator from '../../../Mixins/usesTranslator';

export default {
  mixins: [UsesTranslator],
  template: template,
  props: {
    settings: {
      type: Object,
      default: null
    }
  },
  methods: {
    checkPermissions(configItem) {
      if (configItem.permissions.hasAllPermissions) {
        configItem.permissions.canApproveComments = true;
        configItem.permissions.canEditComments = true;
        configItem.permissions.canRemoveComments = true;
        configItem.permissions.canReplyToComments = true;
        configItem.permissions.canReportAsHam = true;
        configItem.permissions.canReportAsSpam = true;
        configItem.permissions.canUnApproveComments = true;
        configItem.permissions.canViewComments = true;
      } else {
        configItem.permissions.canApproveComments = false;
        configItem.permissions.canEditComments = false;
        configItem.permissions.canRemoveComments = false;
        configItem.permissions.canReplyToComments = false;
        configItem.permissions.canReportAsHam = false;
        configItem.permissions.canReportAsSpam = false;
        configItem.permissions.canUnApproveComments = false;
        configItem.permissions.canViewComments = false;
        configItem.permissions.hasAllPermissions = false;
      }
    }
  },
  data() {
    return {
    };
  }
};
