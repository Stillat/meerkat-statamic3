import Translator from '../../Translation/translator';
import String from '../../Types/string';

export default {
  methods: {
    trans(val): String {
      return Translator.Instance.translate(val);
    },
    transErrorCode(code): String {
      return Translator.Instance?.errorCode(code);
    },
    transFormat(val, replacements) {
      return String.format(this.trans(val), ...replacements);
    }
  }
};
