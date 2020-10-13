import Type from '../../Types/type';

class PagedMetaData {

  constructor() {
    this.currentPage = 1;
    this.totalPages = 1;
    this.totalItems = 1;
    this.itemsPerPage = 1;
  }

  /**
   * Converts an API response object to a new PagedMetaData instance.
   *
   * @param {Object} apiObject The API response object.
   * @returns {PagedMetaData}
   */
  static fromApiObject(apiObject): PagedMetaData {
    let newMetaData = new PagedMetaData();

    newMetaData.currentPage = Type.withDefault(apiObject[PagedMetaData.ApiCurrentPage], 1);
    newMetaData.totalPages = Type.withDefault(apiObject[PagedMetaData.ApiTotalPages], 1);
    newMetaData.totalItems = Type.withDefault(apiObject[PagedMetaData.ApiTotalItems], 1);
    newMetaData.itemsPerPage = Type.withDefault(apiObject[PagedMetaData.ApiItemsPerPage], 1);

    return newMetaData;
  }

}

PagedMetaData.ApiCurrentPage = 'current_page';
PagedMetaData.ApiTotalPages = 'total_pages';
PagedMetaData.ApiTotalItems = 'total_items';
PagedMetaData.ApiItemsPerPage = 'items_per_page';

export default PagedMetaData;
