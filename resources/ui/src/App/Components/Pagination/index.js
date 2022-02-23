import template from './template.html';
import {Range} from '../../../Types/common';
import UsesTranslator from '../../Mixins/usesTranslator';
import {RangeItem, PagedMetaData} from '../../../Data/Paged/common';

const forceSmallSliderCutOff = 10;
const paginatorRangeAdjustment = 2;
const paginatorRangeSizeOffset = 6;
const sharedSeparator = [RangeItem.makeSeparator()];

export default {
  mixins: [UsesTranslator],

  template: template,
  props: {
    displayArrows: {
      type: Boolean,
      default: true
    },
    displayInline: {
      type: Boolean,
      default: false
    },
    displayRange: {
      type: Boolean,
      default: true
    },
    rangeItemsPerSide: {
      type: Number,
      default: 3
    },
    displayPerPage: {
      type: Boolean,
      default: true
    },
    perPage: {
      type: Number,
      default: 10
    },
    perPageOptions: {
      type: Array,
      default: function () {
        return [
          10,
          25,
          50,
          100
        ];
      }
    },
    /**
     * @type PagedMetaData
     */
    pageData: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      instancePerPage: -1,
      visibleRangeWindow: 0
    };
  },
  watch: {
    instancePerPage: function (val, oldVal) {
      if (oldVal > -1) {
        this.$emit('per-page-updated', val);
      }
    }
  },
  methods: {
    //  region Range Generation
    getWindowStart(): Array<Number> {
      return Range.get(1, this.visibleRangeWindow + paginatorRangeAdjustment);
    },
    getWindowEnd(): Array<Number> {
      return Range.get(
        this.pageData.totalPages - (this.visibleRangeWindow + paginatorRangeAdjustment),
        this.pageData.totalPages
      );
    },
    getRangeStart(): Array<Number> {
      return Range.get(1, 2);
    },
    getRangeEnd(): Array<Number> {
      return Range.get(this.pageData.totalPages - 1, this.pageData.totalPages);
    },
    getRangeAdjacent(): Array<Number> {
      return Range.get(
        this.pageData.currentPage - this.rangeItemsPerSide,
        this.pageData.currentPage + this.rangeItemsPerSide
      );
    },
    //  endregion
    //  region Navigation Support
    hasPage(pageNumber: Number) {
      if (this.pageData === null) {
        return false;
      }

      return pageNumber >= 1 && pageNumber <= this.pageData.totalPages;
    },
    moveToPage(pageNumber: Number) {
      if (this.pageData === null) {
        return;
      }

      this.pageData.currentPage = pageNumber;

      this.$emit('page-updated', pageNumber);
    },
    updatePerPage(event) {
      this.$emit('per-page-updated', event.target.value);
      event.preventDefault();
    },
    movePrevious() {
      if (this.pageData === null) {
        return;
      }

      if (this.pageData.currentPage === 1) {
        return;
      }

      this.moveToPage(this.pageData.currentPage - 1);
    },
    moveNext() {
      if (this.pageData === null) {
        return;
      }

      if (this.pageData.currentPage === this.pageData.totalPages) {
        return;
      }

      this.moveToPage(this.pageData.currentPage + 1);
    },
    //  endregion
    //  region Display Logic Helpers
    shouldUseSmallSlider(): Boolean {
      if (this.displayPerPage === true) {
        if (this.pageData.totalPages <= this.instancePerPage && this.pageData.totalPages <= forceSmallSliderCutOff) {
          return true;
        }
      }

      return this.pageData.totalPages < (this.rangePerSide * paginatorRangeAdjustment) + paginatorRangeSizeOffset;
    },
    shouldUseBeginningSlider(): Boolean {
      return this.pageData.currentPage <= this.visibleRangeWindow;
    },
    shouldUseEndSlider(): Boolean {
      return this.pageData.currentPage > (this.pageData.totalPages - this.visibleRangeWindow);
    },
    updateVisibleRange(perSide: Number) {
      this.visibleRangeWindow = perSide * paginatorRangeAdjustment;
    },
    makeRangeItem(pageNumber: Number): RangeItem {
      let newItem = new RangeItem();

      newItem.pageNumber = pageNumber;

      if (this.pageData !== null && this.pageData.currentPage === pageNumber) {
        newItem.isSelected = true;
      }

      return newItem;
    },
    makeRangeArray(range: Array<Number>): Array<RangeItem> {
      let rangeItems = [];

      for (let i = 0; i < range.length; i += 1) {
        rangeItems.push(this.makeRangeItem(range[i]));
      }

      return rangeItems;
    },
    buildSeparatedRangeItems(ranges: Array<Array<Number>>): Array<RangeItem> {
      let rangeItems = [],
        rangeMax = ranges.length - 1;

      for (let i = 0; i < ranges.length; i += 1) {
        rangeItems = rangeItems.concat(this.makeRangeArray(ranges[i]));

        if (i < rangeMax) {
          rangeItems = rangeItems.concat(sharedSeparator);
        }
      }

      return rangeItems;
    }
    //  endregion
  },
  computed: {
    hasMultiplePages(): Boolean {
      if (this.pageData === null) {
        return false;
      }

      return this.pageData.totalPages > 1;
    },
    shouldDisplayPerPageSelection(): Boolean {
      if (this.perPageOptions === null || this.perPageOptions.length === 0) {
        return false;
      }

      if (this.pageData === null) {
        return false;
      }

      return this.pageData.totalItems >= this.perPageOptions[0];
    },
    visibleRange(): Array<RangeItem> {
      if (this.pageData === null) {
        return [];
      }

      let rangeItems = [];

      if (this.shouldUseSmallSlider()) {
        rangeItems = this.makeRangeArray(
          Range.get(1, this.pageData.totalPages)
        );
      } else if (this.shouldUseBeginningSlider()) {
        rangeItems = this.buildSeparatedRangeItems([
          this.getWindowStart(),
          this.getRangeEnd()
        ]);
      } else if (this.shouldUseEndSlider()) {
        rangeItems = this.buildSeparatedRangeItems([
          this.getRangeStart(),
          this.getWindowEnd()
        ]);
      } else {
        rangeItems = this.buildSeparatedRangeItems([
          this.getRangeStart(),
          this.getRangeAdjacent(),
          this.getRangeEnd()
        ]);
      }

      return rangeItems;
    },
    hasPrevious() {
      if (this.pageData === null) {
        return false;
      }

      return this.pageData.currentPage > 1;
    },
    hasNext() {
      if (this.pageData === null) {
        return false;
      }

      return this.pageData.currentPage < this.pageData.totalPages;
    },
    isFirstPage() {
      if (this.pageData === null) {
        return false;
      }

      return this.pageData.currentPage <= 1;
    },
    isLastPage() {
      if (this.pageData === null) {
        return false;
      }

      return this.pageData.currentPage >= this.pageData.totalPages;
    }
  },
  created() {
    this.instancePerPage = this.perPage;
    this.updateVisibleRange(this.rangeItemsPerSide);
  }
};
