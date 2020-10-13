import {canBeStringOrHash} from '../Concerns/canBeStringOrHash';

/**
 * @property {function() : String} toJsonString() Returns the object to a JSON-encoded string.
 * @property {function() : String} toHash() Returns the object as a hash string.
 */
class SearchOptions {

  constructor() {
    canBeStringOrHash(this);

    this.page = 1;
    this.resultsPerPage = 10;
    this.query = {
      order: 'id,desc'
    };
  }

  getRequestData(): Object {
    return Object.assign({}, {
      page: this.page,
      resultsPerPage: this.resultsPerPage
    }, this.query);
  }

}

export default SearchOptions;
