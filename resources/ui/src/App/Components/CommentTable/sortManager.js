import String from '../../../Types/string';

class SortManager {

  constructor() {
    this.columns = {};
    this.orders = {};
    this.sortIndex = [];
    this.hasMultipleOrders = false;
    this.sortString = '';
  }

  /**
   * Sets which columns to consider when building internal sort state.
   *
   * @param {Object} columns The columns to set.
   */
  setColumns(columns) {
    this.columns = columns;
    this.buildSortIndex();
  }

  /**
   * Toggles a column's sort direction from desc to asc, and then none.
   *
   * @param {string} column The column name.
   */
  toggleColumn(column) {
    if (this.columns[column] === SortManager.Desc) {
      this.columns[column] = SortManager.Asc;
    } else if (this.columns[column] === SortManager.Asc) {
      this.columns[column] = SortManager.None;
    } else {
      this.columns[column] = SortManager.Desc;
    }

    this.rebuildSortIndex();
  }

  /**
   * Sets the internal sort string and parses it.
   *
   * @param {String} sortString The sort string.
   */
  setSortString(sortString: string) {
    if (String.hasValue(sortString) === false) {
      return;
    }

    let parts = sortString.split('|'),
      columnsToSet = this.columns;

    if (parts.length === 0) {
      return;
    }

    for (let i = 0; i < parts.length; i += 1) {
      let columnParts = parts[i].split(',');

      if (columnParts.length !== 2) {
        continue;
      }

      let column = columnParts[0],
        direction = columnParts[1],
        mapDirection = SortManager.None;

      if (String.hasValue(column) === false || String.hasValue(direction) === false) {
        continue;
      }

      column = column.trim();
      direction = direction.toLowerCase().trim();

      if (direction === 'asc') {
        mapDirection = SortManager.Asc;
      } else if (direction === 'desc') {
        mapDirection = SortManager.Desc;
      }

      columnsToSet[column] = mapDirection;
    }

    this.setColumns(columnsToSet);
  }

  /**
   * Builds the internal sort string that can be used to communicate with the server.
   */
  buildSortString() {
    let parts = [];

    for (let i = 0; i < this.sortIndex.length; i += 1) {
      let sortOrder = this.columns[this.sortIndex[i].column];

      if (sortOrder === SortManager.Asc) {
        parts.push(this.sortIndex[i].column + ',asc');
      } else if (sortOrder === SortManager.Desc) {
        parts.push(this.sortIndex[i].column + ',desc');
      }
    }

    if (parts.length === 0) {
      this.sortString = '';
    } else {
      this.sortString = parts.join('|');
    }
  }

  /**
   * Rebuilds the internal sort index.
   */
  rebuildSortIndex() {
    let tempSortIndex = [],
      indexMap = {}, currentIndex = 0,
      totalSorting = 0,
      orderedIndex = this.sortIndex.sort((a, b) => (a.order > b.order) ? 1 : -1),
      indexFinal = [], mapFinal = {};

    for (let key in this.columns) {
      if (this.columns[key] !== SortManager.None) {
        totalSorting += 1;
      }
    }

    for (let i = 0; i < orderedIndex.length; i += 1) {
      if (this.columns[orderedIndex[i].column] !== SortManager.None) {
        indexMap[orderedIndex[i].column] = orderedIndex[i].order;
        currentIndex = i;
      }
    }

    if (totalSorting === 0) {
      this.orders = {};
      this.sortIndex = [];
      this.hasMultipleOrders = false;

      this.buildSortString();

      return;
    }

    if (currentIndex > 0) {
      currentIndex += 1;
    }

    for (let key in this.columns) {
      if (this.columns[key] !== SortManager.None) {
        let curSortIndex = currentIndex;

        if (typeof indexMap[key] !== 'undefined') {
          curSortIndex = indexMap[key];
        }

        tempSortIndex.push({
          column: key,
          order: curSortIndex
        });

        currentIndex += 1;
      }
    }

    // Resort.
    tempSortIndex = tempSortIndex.sort((a, b) => (a.order > b.order) ? 1 : -1);

    currentIndex = 0;

    for (let i = 0; i < tempSortIndex.length; i += 1) {
      indexFinal.push({
        column: tempSortIndex[i].column,
        order: currentIndex
      });

      mapFinal[tempSortIndex[i].column] = currentIndex + 1;

      currentIndex += 1;
    }

    this.orders = mapFinal;
    this.sortIndex = indexFinal;
    this.hasMultipleOrders = this.sortIndex.length > 1;

    this.buildSortString();
  }

  /**
   * Builds the initial internal sort index.
   */
  buildSortIndex() {
    let index = 0, tempSortIndex = [], tempOrderMap = {};

    for (let key in this.columns) {
      if (this.columns[key] !== SortManager.None) {
        tempSortIndex.push({
          column: key,
          order: index
        });

        tempOrderMap[key] = index + 1;
      }

      index += 1;
    }

    this.orders = tempOrderMap;
    this.sortIndex = tempSortIndex;
    this.hasMultipleOrders = this.sortIndex.length > 1;

    this.buildSortString();
  }

}

SortManager.Asc = 1;
SortManager.Desc = -1;
SortManager.None = 0;

export default SortManager;
