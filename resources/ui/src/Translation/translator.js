class Translator {

  translate(val) : String {
    throw new Error('Translator instance not supplied.');
  }

  errorCode(errorCode) : String {
    let targetKey = this.translate('codes.' + errorCode);

    return this.translate(targetKey);
  }

}

/**
 * A shared translator implementation instance.
 *
 * @type {(Translator|null)}
 */
Translator.Instance = new Translator();

export default Translator;
