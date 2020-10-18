import Type from '../../Types/type';

class SpamGuard {

  constructor() {
    this.name = '';
    this.class = '';
    this.enabled = false;
  }

  static fromApiObject(apiObject): SpamGuard {
    let spamGuard = new SpamGuard();

    spamGuard.name = Type.withDefault(apiObject[SpamGuard.ApiName], '');
    spamGuard.class = Type.withDefault(apiObject[SpamGuard.ApiClass], '');
    spamGuard.enabled = Type.withDefault(apiObject[SpamGuard.ApiEnabled], true);

    return spamGuard;
  }

}

SpamGuard.ApiName = 'name';
SpamGuard.ApiClass = 'class';
SpamGuard.ApiEnabled = 'enabled';

export default SpamGuard;

