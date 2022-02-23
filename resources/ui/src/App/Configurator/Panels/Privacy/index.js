import template from './template.html';
import SettingsResponse from '../../../../Http/Responses/settingsResponse';
import UsesTranslator from '../../../Mixins/usesTranslator';

export default {
  mixins: [UsesTranslator],
  template: template,
  data() {
    return {
      showDefaults: false
    };
  },
  props: {
    settings: {
      type: Object,
      default: null
    }
  }
};
