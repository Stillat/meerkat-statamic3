import Type from '../../Types/type';

export function applyCollectionSelectable(instance) {
  instance.allSelected = false;

  instance.toggleSelections = function () {
    if (this.allSelected === true) {
      this.unSelectAll();
    } else {
      this.selectAll();
    }
  }.bind(instance);

  instance.selectAll = function () {
    for (let i = 0; i < this.length; i += 1) {
      if (Type.hasValue(this[i], 'isSelected')) {
        this[i].isSelected = true;
      }
    }

    this.allSelected = true;
  }.bind(instance);

  instance.unSelectAll = function () {
    for (let i = 0; i < this.length; i += 1) {
      if (Type.hasValue(this[i], 'isSelected')) {
        this[i].isSelected = false;
      }
    }

    this.allSelected = false;
  }.bind(instance);
}

export function applyErrorState(instance) {
  instance.errors = [];
  instance.hasErrors = false;
}

export function enforceCollectionType(instance, type) {
  instance._typeEnforced = type;
  instance._outerType = instance.constructor.name;
  instance._pushProxy = instance.push;

  instance.push = function (val) {
    if (typeof val.constructor !== 'undefined') {
      if (val.constructor.name === this._typeEnforced) {
        if (Type.hasValue(val['_internalCollection'])) {
          val._internalCollection = this;
        }

        this._pushProxy(val);

        return;
      }
    }

    throw new Error(this._outerType + ' expects type ' +
      this._typeEnforced + '. ' + val.constructor.name + ' provided');
  }.bind(instance);
}
