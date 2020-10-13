import Translator from '../Translation/translator';
import String from '../Types/string';
import Addon from 'addon';

class StatamicTranslator extends Translator {

  translate(val): String {
    return window.__(String.format('{0}::{1}', Addon.codeAddonName, val));
  }

}

export default StatamicTranslator;
