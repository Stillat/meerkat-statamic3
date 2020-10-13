import Translator from './Translation/translator';

export default function trans(message): String {
  return Translator.Instance.translate(message);
}
