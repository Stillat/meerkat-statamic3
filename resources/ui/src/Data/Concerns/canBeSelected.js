export function canBeSelected(instance) {
  instance.isSelected = false;

  instance.select = function () {
    this.isSelected = true;
  }.bind(instance);

  instance.unselect = function () {
    this.isSelected = false;
  }.bind(instance);
}
