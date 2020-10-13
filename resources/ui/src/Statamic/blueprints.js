import entryTemplate from './blueprintEntry.html';

/**
 * Provides utilities for adding entries to the
 * Blueprints Statamic Control list at runtime.
 */
class Blueprints {

  /**
   * Adds an entry to the Blueprints Collections list.
   *
   * @param svgIcon
   * @param title
   * @param url
   */
  addCollectionsEntry(svgIcon, title, url) {
    this.addTableEntry(
      this.getTable(Blueprints.TABLE_COLLECTIONS),
      svgIcon,
      title
    );
  }

  /**
   * Adds an entry to the Blueprints Taxonomies list.
   *
   * @param svgIcon
   * @param title
   * @param url
   */
  addTaxonomiesEntry(svgIcon, title, url) {
    this.addTableEntry(
      this.getTable(Blueprints.TABLE_TAXONOMIES),
      svgIcon,
      title
    );
  }

  /**
   * Adds an entry to the Blueprints Globals list.
   *
   * @param svgIcon
   * @param title
   * @param url
   */
  addGlobalsEntry(svgIcon, title, url) {
    this.addTableEntry(
      this.getTable(Blueprints.TABLE_GLOBALS),
      svgIcon,
      title
    );
  }

  /**
   * Adds an entry to the Blueprints Asset Containers list.
   *
   * @param svgIcon
   * @param title
   * @param url
   */
  addAssetContainersEntry(svgIcon, title, url) {
    this.addTableEntry(
      this.getTable(Blueprints.TABLE_CONTAINERS),
      svgIcon,
      title
    );
  }

  /**
   * Adds an entry to the Blueprints Forms list.
   *
   * @param svgIcon
   * @param title
   * @param url
   */
  addFormsEntry(svgIcon, title, url) {
    this.addTableEntry(
      this.getTable(Blueprints.TABLE_FORMS),
      svgIcon,
      title
    );
  }

  /**
   * Adds an entry to the Blueprints Other list.
   *
   * @param svgIcon
   * @param title
   * @param url
   */
  addOtherEntry(svgIcon, title, url) {
    let allTables = window.jQuery('table.data-table'),
      otherTable = allTables[allTables.length - 1];

    this.addTableEntry(
      otherTable,
      svgIcon,
      title,
      url
    );
  }

  getTable(table) {
    return window.jQuery('table.data-table')[table];
  }

  /**
   * Adds a new entry to the provided table.
   *
   * @param table
   * @param svgIcon
   * @param url
   * @param title
   */
  addTableEntry(table, svgIcon, title, url) {
    let newEntry = entryTemplate;

    newEntry = newEntry.replace('@svg', svgIcon);
    newEntry = newEntry.replace('@title', title);
    newEntry = newEntry.replace('@url', url);

    window.jQuery(table).find('tbody').append(window.jQuery(
      newEntry
    ));
  }

}

Blueprints.TABLE_COLLECTIONS = 0;
Blueprints.TABLE_TAXONOMIES = 1;
Blueprints.TABLE_GLOBALS = 2;
Blueprints.TABLE_CONTAINERS = 3;
Blueprints.TABLE_FORMS = 4;
Blueprints.TABLE_OTHER = 5;

export default Blueprints;
