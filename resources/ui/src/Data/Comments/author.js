import String from '../../Types/string';
import Type from '../../Types/type';

class Author {

  constructor() {
    this.id = null;
    this.initials = '';
    this.email = '';
    this.hasUser = false;
    this.name = '';
    this.userAgent = '';
    this.userIp = '';
    this.webAddress = '';
  }

  /**
   * Tests if the author contains an email address value.
   *
   * @returns {boolean}
   */
  hasEmailAddress(): Boolean {
    return String.hasValue(this.email);
  }

  /**
   * Tests if the author contains a web address value.
   *
   * @returns {boolean}
   */
  hasWebAddress(): Boolean {
    return String.hasValue(this.webAddress);
  }

  /**
   * Converts an API object into an Author instance.
   *
   * @param {Object} apiObject The result from the API.
   * @returns {Author}
   */
  static fromApiObject(apiObject): Author {
    let author = new Author();

    author.id = Type.withDefault(apiObject[Author.ApiId], null);
    author.initials = Type.withDefault(apiObject[Author.ApiInitials], '');
    author.email = Type.withDefault(apiObject[Author.ApiEmail], '');
    author.hasUser = Type.withDefault(apiObject[Author.ApiHasUser], false);
    author.name = Type.withDefault(apiObject[Author.ApiName], '');
    author.userAgent = Type.withDefault(apiObject[Author.ApiUserAgent], '');
    author.userIp = Type.withDefault(apiObject[Author.ApiUserIp], '');
    author.webAddress = Type.withDefault(apiObject[Author.ApiWebAddress], '');

    return author;
  }

}

Author.ApiId = 'id';
Author.ApiInitials = 'initials';
Author.ApiWebAddress = 'url';
Author.ApiEmail = 'email';
Author.ApiHasUser = 'has_user';
Author.ApiName = 'name';
Author.ApiUserAgent = 'user_agent';
Author.ApiUserIp = 'user_ip';

export default Author;
