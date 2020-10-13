import template from '../imageBasedDriver.html';
import md5 from 'crypto-js/md5';

export default {
  template: template,
  props: {
    author: {
      type: Object,
      default: null
    },
    isRounded: {
      type: Boolean,
      default: true
    }
  },
  methods: {
    getAvatarUrl() {
      let addressToHash = 'example@example.org';

      if (this.author !== null) {
        addressToHash = this.author.email.trim().toLowerCase();
      }

      let hashedAddress = md5(addressToHash);

      return 'https://avatars.dicebear.com/v2/jdenticon/' + hashedAddress + '.svg';
    }
  }
};
