import SimpleDriver from './Simple';
import InitialsDriver from './Initials';
import GravatarDriver from './Gravatar';
import IdenticonDriver from './Identicon';
import JdenticonDriver from './Jdenticon';

(function () {
  window.meerkatExtend.Extend.Avatars.registerDriver('initials', 'Initials', InitialsDriver);
  window.meerkatExtend.Extend.Avatars.registerDriver('simple', 'Simple Initials', SimpleDriver);
  window.meerkatExtend.Extend.Avatars.registerDriver('gravatar', 'Gravatar', GravatarDriver);
  window.meerkatExtend.Extend.Avatars.registerDriver('identicon', 'Identicon (using DiceBear)', IdenticonDriver);
  window.meerkatExtend.Extend.Avatars.registerDriver('jdenticon', 'Jdenticon (using DiceBear)', JdenticonDriver);
})();
