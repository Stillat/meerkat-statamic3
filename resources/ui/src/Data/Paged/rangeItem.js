class RangeItem {
  constructor() {
    this.isSelected = false;
    this.isSeparator = false;
    this.pageNumber = 1;
  }

  static makeSeparator(): RangeItem {
    let newItem = new RangeItem();

    newItem.isSeparator = true;

    return newItem;
  }

}

export default RangeItem;
