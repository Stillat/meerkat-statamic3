import template from './template.html';
import CommentRepository from '../../Repositories/commentRepository';
import paginator from '../Components/Pagination';
import UsesTranslator from '../Mixins/usesTranslator';
import CommentTable from '../Components/CommentTable';
import SearchOptions from '../../Data/Comments/searchOptions';
import Loader from '../Components/Loader';
import Environment from '../../Config/environment';
import Filter from '../../Data/Filters/filter';
import SortManager from '../Components/CommentTable/sortManager';
import Url from '../../Types/url';
import {getDefaultFilter} from '../../Data/Filters/defaultFilterApplicator';
import Endpoints from '../../Http/endpoints';
import OverviewProvider from '../../Reporting/overviewProvider';
import ControlPanelApplication from '../controlPanelApplication';
import TaskObserver from '../../Tasks/taskObserver';

const syncjs = require('syncjs');

require('./style.less');

export default {
  mixins: [UsesTranslator],
  template: template,
  components: {
    'comment-table': CommentTable,
    'loader': Loader,
    'paginator': paginator
  },
  data() {
    return {
      state: {
        hasLoadedInitial: false,
        loadingInitial: false,
        loadingData: false,
        lastPageRequest: 1,
        lastPerPageRequest: -1,
        initialPerPage: 10,
        initialSortString: null,
        tableIsAvailable: false,
        activeFilterId: 0,
        activeFilterName: 'all',
        isCheckingAllForSpam: false,
        statusMessage: '',
        showStatusMessage: false
      },
      defaultFilters: ['all', 'pending', 'published', 'spam'],
      searchOptions: new SearchOptions(),
      commentRepo: new CommentRepository(),
      commentData: null,
      exportLinks: {
        csv: Endpoints.url(Endpoints.ExportCsv) + '?download=true',
        json: Endpoints.url(Endpoints.ExportJson) + '?download=true'
      },
      permissions: null,
      canCheckForSpam: false,
      spamTaskObserver: null
    };
  },
  methods: {
    removeAllSpam() {
      this.$refs.commentTable.performBulkAction('remove-spam');
    },
    hasEditorOpen() {
      if (this.commentData === null) {
        return false;
      }

      for (let i = 0; i < this.commentData.comments.length; i += 1) {
        let comment = this.commentData.comments[i];

        if (comment.state.isEditing === true || comment.state.isReplying === true) {
          return true;
        }
      }

      return false;
    },
    reloadStateAnyway() {
      this.commentData.comments.cancelAllEditing();
      this.commentData.comments.cancelAllReplying();
      this.state.showStatusMessage = false;
      this.$refs.commentTable.exitFocusMode();
      this.loadCommentData();
    },
    checkForSpam() {
      this.state.isCheckingAllForSpam = true;
      this.commentRepo.checkForSpam().then(function (response) {
        if (response.success === true) {
          ControlPanelApplication.current().controlPanel.message().success(
            this.trans('actions.check_all_spam_task_created')
          );

          this.spamTaskObserver.watch(response.taskId);
        } else {
          this.state.isCheckingAllForSpam = false;
          ControlPanelApplication.current().controlPanel.message().error(
            this.trans('actions.check_all_spam_error')
          );
        }
      }.bind(this)).catch(function () {
        this.state.isCheckingAllForSpam = false;
        ControlPanelApplication.current().controlPanel.message().error(this.trans('actions.check_all_spam_error'));
      }.bind(this));
    },
    onCommentsGlobalSpamCheckComplete() {
      if (this.hasEditorOpen() === false) {
        this.loadCommentData();
      } else {
        this.state.statusMessage = this.trans('actions.check_all_spam_complete_open_editors');
        this.state.showStatusMessage = true;
      }
    },
    onSpamTaskComplete() {
      this.state.isCheckingAllForSpam = false;
      this.spamTaskObserver.ensureStopped();
      ControlPanelApplication.current().controlPanel.message().success(
        this.trans('actions.check_all_spam_complete')
      );

      OverviewProvider.Instance.refresh();
      syncjs.Hubs.comments().globalSpamCheckComplete();
    },
    onSpamTaskCanceled() {
      this.state.isCheckingAllForSpam = false;
      this.spamTaskObserver.ensureStopped();
      ControlPanelApplication.current().controlPanel.message().error(
        this.trans('actions.check_all_spam_canceled')
      );
    },
    onSpamTaskError() {
      this.state.isCheckingAllForSpam = false;
      this.spamTaskObserver.ensureStopped();
      ControlPanelApplication.current().controlPanel.message().error(this.trans('actions.check_all_spam_error'));
    },
    onTableAvailable(table) {
      this.state.tableIsAvailable = true;

      this.$refs.commentTable.setSortString(this.state.initialSortString);
    },
    onSearchUpdated(terms) {
      this.searchOptions.query.terms = terms;

      this.loadCommentData();
    },
    checkFilters(comments) {
      let filterComments = this.commentData.comments.whereIn(comments);

      this.$refs.commentTable.checkFilters(filterComments);
      OverviewProvider.Instance.refresh();
    },
    onCommentsPublished(comments) {
      this.checkFilters(comments);
    },
    onCommentsUnpublished(comments) {
      this.checkFilters(comments);
    },
    onCommentsMarkedAsSpam(comments) {
      this.checkFilters(comments);
    },
    onCommentsMarkedAsHam(comments) {
      this.checkFilters(comments);
    },
    onCommentsRemoved(comments) {
      this.loadCommentData();
    },
    onFilterChanged(filter: Filter) {
      this.state.activeFilterId = filter.id;
      this.state.activeFilterName = filter.internalName;

      this.updateHistoryState();
      this.searchOptions = filter.adjustOptions(this.searchOptions);

      this.loadCommentData();
    },
    updateHistoryState() {
      Environment.pushHistoryState(this.state.activeFilterName);
    },
    onOrderUpdated(manager: SortManager) {
      this.searchOptions.query.order = manager.sortString;

      this.loadCommentData();
    },
    updateQueryWithPerPage(perPageCount) {
      Environment.Preferences.updatePerPage(perPageCount);

      if (this.state.lastPerPageRequest > -1 && this.state.lastPerPageRequest === perPageCount) {
        return;
      }

      this.state.lastPerPageRequest = perPageCount;
      this.searchOptions.resultsPerPage = perPageCount;

      this.loadCommentData().then(function () {
        this.$nextTick(function () {
          Environment.scrollTop();
        });
      }.bind(this));
    },
    updateQueryWithPage(pageNumber) {
      if (this.state.lastPageRequest === pageNumber) {
        return;
      }

      this.state.lastPageRequest = pageNumber;
      this.searchOptions.page = pageNumber;

      this.loadCommentData();
    },
    onRefreshRequested() {
      this.loadCommentData();
    },
    loadCommentData() {
      if (this.state.hasLoadedInitial === false) {
        this.state.loadingInitial = true;
      }

      this.state.loadingData = true;

      return new Promise(function (resolve, reject) {
        this.commentRepo.search(this.searchOptions).then(function (response) {

          if (this.state.hasLoadedInitial === false) {
            this.state.hasLoadedInitial = true;
            this.state.loadingInitial = false;
          }

          this.commentData = response;
          this.state.initialSortString = response.sortString;

          this.state.loadingData = false;
          resolve();
        }.bind(this)).catch(function (e) {
          reject(e);
        });
      }.bind(this));
    },
    applyFromDefaultFilter(currentUrlRequest) {
      if (this.defaultFilters.includes(currentUrlRequest)) {
        let defaultFilter = getDefaultFilter(currentUrlRequest);

        if (defaultFilter !== null) {
          this.state.activeFilterId = defaultFilter.id;
          this.state.activeFilterName = defaultFilter.internalName;
          this.onFilterChanged(defaultFilter);
        }
      }
    }
  },
  created() {
    this.spamTaskObserver = new TaskObserver();
    this.spamTaskObserver.on('error', this.onSpamTaskError.bind(this));
    this.spamTaskObserver.on('complete', this.onSpamTaskComplete.bind(this));
    this.spamTaskObserver.on('canceled', this.onSpamTaskCanceled.bind(this));

    this.permissions = Environment.getPermissions();

    if (this.permissions.canReportAsHam && this.permissions.canReportAsSpam) {
      this.canCheckForSpam = true;
    }

    let currentUrlRequest = Url.currentLastValue().toLowerCase();

    this.applyFromDefaultFilter(currentUrlRequest);

    window.onpopstate = function (event) {
      let poppedValue = Url.lastValue(event.state.urlPath);

      this.applyFromDefaultFilter(poppedValue);
    }.bind(this);

    syncjs.Hubs.comments().handledBy(this);
    this.loadCommentData();

    this.state.initialPerPage = Environment.Preferences.getPerPage();
    this.searchOptions.resultsPerPage = this.state.initialPerPage;
  }
};
