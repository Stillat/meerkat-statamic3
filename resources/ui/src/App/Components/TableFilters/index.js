import template from './template.html';
import UsesTranslator from '../../Mixins/usesTranslator';
import {AllFilter, IsSpamFilter, PendingFilter, PublishedFilter} from '../../../Data/Filters/defaultFilters';
import Guid from '../../../Types/guid';
import Environment from '../../../Config/environment';
import {debounce} from '../../../utils';
import OverviewProvider from '../../../Reporting/overviewProvider';
import OverviewResponse from '../../../Http/Responses/Reporting/overviewResponse';
import Type from '../../../Types/type';

require('./style.less');

export default {
  template: template,
  mixins: [UsesTranslator],
  props: {
    activeFilterId: {
      type: Number,
      default: 0
    }
  },
  data: function () {
    return {
      searchId: 'meerkat-search-' + Guid.newGuid(),
      searchFilter: '',
      filters: [],
      state: {
        isOpen: false
      }
    };
  },
  methods: {
    filterRequiresUpdate(comments) {
      for (let i = 0; i < this.filters.length; i += 1) {
        if (Type.hasValue(this.filters[i]['shouldReload'])) {
          if (this.filters[i].shouldReload(comments)) {
            return true;
          }
        }
      }

      return false;
    },
    updateFilterDisplays(report: OverviewResponse) {
      if (report.success) {
        for (let i = 0; i < this.filters.length; i += 1) {
          if (Type.hasValue(this.filters[i]['updateState'])) {
            this.filters[i].updateState(report);
          }
        }
      }
    },
    resetSearch() {
      if (this.searchFilter === '') {
        return;
      }

      this.searchFilter = '';

      this.$emit('search-updated', this.searchFilter);
    },
    searchEvent: debounce(function (e) {
      this.$emit('search-updated', this.searchFilter);
    }, 750),
    toggle() {
      this.state.isOpen = !this.state.isOpen;
      Environment.Preferences.updateDisplayTableFilters(this.state.isOpen);

      this.checkForFocus();
    },
    checkForFocus() {
      if (this.state.isOpen) {
        this.$nextTick(function () {
          Environment.ContextJquery('#' + this.searchId).focus();
        });
      }
    },
    onFilterClick(filter) {
      if (filter.id !== this.activeFilterId) {
        this.activeFilterId = filter.id;
        this.$emit('filter-changed', filter);
      }
    }
  },
  created() {
    this.filters.push(new AllFilter());
    this.filters.push(new PendingFilter());
    this.filters.push(new IsSpamFilter());
    this.filters.push(new PublishedFilter());

    OverviewProvider.Instance.on('updated', this.updateFilterDisplays);

    if (OverviewProvider.Instance.hasData()) {
      this.updateFilterDisplays(OverviewProvider.Instance.report);
    }

    this.state.isOpen = Environment.Preferences.getDisplayTableFilters();
    this.checkForFocus();
  }
};
