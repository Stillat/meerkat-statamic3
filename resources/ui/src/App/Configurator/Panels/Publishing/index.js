import template from './template.html';
import SettingsResponse from '../../../../Http/Responses/settingsResponse';
import UsesTranslator from '../../../Mixins/usesTranslator';

export default {
  mixins: [UsesTranslator],
  template: template,
  props: {
    settings: {
      type: SettingsResponse,
      default: null
    }
  },
  data() {
    return {
      autoPublish: true,
      autoClose: false,
      closeDays: 2
    };
  }
};
