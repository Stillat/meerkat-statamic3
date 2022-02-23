import template from './template.html';
import SettingsResponse from '../../../../Http/Responses/settingsResponse';
import UsesTranslator from '../../../Mixins/usesTranslator';
import Loader from '../../../Components/Loader';
import SettingsRepository from '../../../../Repositories/settingsRepository';

export default {
  mixins: [UsesTranslator],
  template: template,
  components: {
    'loader': Loader
  },
  props: {
    settings: {
      type: Object,
      default: null
    }
  },
  computed: {
    hasAkismetSettings() {
      let curApiKey = this.$parent.settings.items['akismet.api_key'].value,
        curFrontPage = this.$parent.settings.items['akismet.front_page'].value;

      return curApiKey.trim().length > 0 && curFrontPage.trim().length > 0;
    }
  },
  methods: {
    validateConfiguration() {
      let curApiKey = this.$parent.settings.items['akismet.api_key'].value,
        curFrontPage = this.$parent.settings.items['akismet.front_page'].value;

      this.state.currentMessage = this.trans('config.validate_akismet_validating');
      this.state.isValidating = true;

      SettingsRepository.Instance.validateAkismet(curApiKey, curFrontPage)
        .then(function (result) {
          this.state.currentMessage = result.message;
          this.state.isValidating = false;
        }.bind(this))
        .catch(function () {
          this.state.currentMessage = this.trans('config.validate_akismet_failure');
          this.state.isValidating = false;
        }.bind(this));
    },
    guardUpdated() {
      this.$parent.updateGuardConfiguration();
    }
  },
  data() {
    return {
      state: {
        currentMessage: '',
        isValidating: false
      }
    };
  }
};
