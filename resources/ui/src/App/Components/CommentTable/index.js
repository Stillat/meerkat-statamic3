import {
  BulkApproveHandler,
  BulkDeleteHandler, BulkNotSpamHandler, BulkRemoveAllSpam, BulkSpamHandler,
  BulkUnapproveHandler,
  EditCommentHandler,
  ReplyCommentHandler
} from './Handlers';
import template from './template.html';
import CommentResponse from '../../../Http/Responses/commentResponse';
import Environment from '../../../Config/environment';
import AvatarDriverRegistry from '../../../Extend/Avatars/avatarDriverRegistry';
import AuthorDisplay from '../AuthorDisplay';
import Comment from '../../../Data/Comments/comment';
import CommentDisplay from '../CommentDisplay';
import CommentEditor from '../CommentEditor';
import ReplyEditor from '../ReplyEditor';
import UsesTranslator from '../../Mixins/usesTranslator';
import Type from '../../../Types/type';
import String from '../../../Types/string';
import ActionState from '../../actionState';
import ActionHandler from '../../Mixins/actionHandler';
import TableFilters from '../TableFilters';
import SortManager from './sortManager';
import SortIndicator from './sortIndicator';
import EmptyIndicator from '../EmptyIndicator';
import OverviewProvider from '../../../Reporting/overviewProvider';
import OverviewResponse from '../../../Http/Responses/Reporting/overviewResponse';

const syncjs = require('syncjs');

require('./style.less');

export default {
  mixins: [UsesTranslator, ActionHandler],
  template: template,
  components: {
    'table-filters': TableFilters,
    'author-display': AuthorDisplay,
    'comment-display': CommentDisplay,
    'comment-editor': CommentEditor,
    'reply-editor': ReplyEditor,
    'sort-indicator': SortIndicator,
    'empty-indicator': EmptyIndicator
  },
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    comments: {
      type: Object,
      default: null
    },
    displayFocusMode: {
      type: Boolean,
      default: false
    },
    activeFilterId: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      sortManager: null,
      currentAction: null,
      avatarDriver: null,
      state: {
        totalCount: 0
      },
      permissions: null,
      handlers: {
        'edit': EditCommentHandler,
        'reply': ReplyCommentHandler
      },
      singleSelectTranslation: this.trans('actions.select_comment'),
      canUseBulkActions: false,
      currentBulkAction: 'approve',
      bulkHandlers: {
        'approve': BulkApproveHandler,
        'unapprove': BulkUnapproveHandler,
        'delete': BulkDeleteHandler,
        'mark-spam': BulkSpamHandler,
        'mark-ham': BulkNotSpamHandler,
        'remove-spam': BulkRemoveAllSpam
      }
    };
  },
  computed: {
    hasData() {
      if (Type.hasValue(this.comments) === false) {
        return false;
      }

      return this.comments.comments.length > 0;
    },
    shouldDisplayThread() {
      if (Type.hasValue(this.comments) === false) {
        return false;
      }

      return this.comments.threads.length > 1;
    },
    hasCommentResponse() {
      return Type.hasValue(this.comments);
    },
    selectedCount: function () {
      let selectedCount = 0;

      if (Type.hasValue(this.comments) === false) {
        return selectedCount;
      }

      for (let i = 0; i < this.comments.comments.length; i += 1) {
        if (this.comments.comments[i].isSelected === true) {
          selectedCount += 1;
        }
      }

      return selectedCount;
    },
    hasSelection: function () {
      let hasSelection = false;

      if (Type.hasValue(this.comments) === false) {
        return hasSelection;
      }

      for (let i = 0; i < this.comments.comments.length; i += 1) {
        if (this.comments.comments[i].isSelected === true) {
          hasSelection = true;
          break;
        }
      }

      return hasSelection;
    },
    tableClasses: function () {
      return {
        'opacity-50': this.loading,
        'meerkat__comment-table--focus': (this.selectedCount === 0 && this.displayFocusMode)
      };
    }
  },
  methods: {
    checkFilters(comments) {
      if (this.$refs.tableFilters.filterRequiresUpdate(comments)) {
        this.$emit('data-update-requested');
      }
    },
    checkStateFromProvider(report: OverviewResponse) {
      if (report.success) {
        this.state.totalCount = report.total;

        if (report.total === 0) {
          this.clearData();
        }

        if (Type.hasValue(this.comments) === true && this.comments.comments.length === 0) {
          if (report.total > 0) {
            this.$emit('data-update-requested');
          }
        }
      }
    },
    exitFocusMode() {
      this.displayFocusMode = false;
    },
    clearData() {
      this.closeAllActionDialogs([]);
      this.comments.clear();
    },
    updateSortFromHeader(columnId) {
      if (String.hasValue(columnId)) {
        this.sortManager.toggleColumn(columnId);
        this.updateStateFromOrder();
      }
    },
    setSortString(sortString: String) {
      this.sortManager.setSortString(sortString);
    },
    updateStateFromOrder() {
      this.$emit('order-changed', this.sortManager);
    },
    onConfigAvatarUpdated(config) {
      if (Type.hasValue(config)) {
        if (config.length > 0) {
          this.avatarDriver = AvatarDriverRegistry.getDriverName(config[0]);
        }
      }
    },
    onConfigUserAvailable() {
      this.permissions = Environment.getPermissions();

      if (this.permissions.canApproveComments || this.permissions.canEditComments ||
          this.permissions.canRemoveComments || this.permissions.canReportAsHam ||
          this.permissions.canReportAsSpam || this.permissions.canUnApproveComments) {
        this.canUseBulkActions = true;
      }
    },
    onFilterChange(filter) {
      this.$emit('filter-changed', filter);
    },
    onSearchUpdated(terms) {
      this.$emit('search-updated', terms);
    },
    closeAllActionDialogs(comments) {
      this.forceDismissBulkActions();
      this.$refs.commentDisplay.forEach(function (d) {
        d.forceDismiss();
      });
    },
    getSelectedIds() {
      let ids = [],
        selectedComments = this.comments.comments.getSelected();

      for (let i = 0; i < selectedComments.length; i += 1) {
        ids.push(selectedComments[i].id);
      }

      return ids;
    },
    getCommentClasses: function (comment: Comment) {
      return {
        'meerkat__comment-row--focused': (comment.state.isEditing || comment.state.isReplying),
        'meerkat__comment-row--pending': (comment.published || comment.hasBeenCheckedForSpam === false),
        'meerkat__comment--row--spam': (comment.hasBeenCheckedForSpam && comment.isSpam === true),
        'meerkat__comment-row--selected row-selected': comment.isSelected
      };
    },
    checkForDismiss() {
      if (this.currentAction !== null && this.currentAction.display === true && this.currentAction.canDismiss()) {
        this.currentAction.dismiss();
      }
    },
    forceDismissBulkActions() {
      if (this.currentAction !== null) {
        this.currentAction.dismiss();
      }
      this.currentAction = null;
    },
    cancelBulkActions() {
      this.comments.comments.unSelectAll();
    },
    performBulkAction(action) {
      if (Type.hasValue(this.bulkHandlers[action])) {
        let bulkHandler = new this.bulkHandlers[action](null);

        bulkHandler.commentIds = this.getSelectedIds();

        this.confirm(bulkHandler)
          .onConfirm((state: ActionState) => {
            state.proceed();
          })
          .onComplete(function () {
            this.comments.comments.unSelectAll();
          }.bind(this));
      }
    },
    performAction(action, comment) {
      if (Type.hasValue(this.handlers[action])) {
        this.confirm(new this.handlers[action](comment))
          .onConfirm((state: ActionState) => {
            state.proceed();
          });
      }
    },
    performActionNow(action, comment) {
      if (Type.hasValue(this.handlers[action])) {
        this.confirm(new this.handlers[action](comment))
          .onConfirm((state: ActionState) => {
            state.proceed();
          })
          .onComplete(function () {
            this.displayFocusMode = false;
          }.bind(this)).start();
      }
    },
    disableFocusMode() {
      this.displayFocusMode = false;
    },
    beforeReply(comment) {
      this.displayFocusMode = true;
      this.comments.comments.draftAllReplying();
    },
    beforeEdit(comment) {
      this.displayFocusMode = true;
      this.comments.comments.draftAllEditing();
    }
  },
  mounted() {
    this.$emit('table-available', this);

    this.$keys.bind('alt+shift+f', function () {
      this.$refs.tableFilters.toggle();
    }.bind(this));
  },
  created() {
    syncjs.Hubs.config().handledBy(this);
    syncjs.Hubs.comments().handledBy(this)
      .reactsToInstance(false)
      .redirectTo(this.closeAllActionDialogs);

    let sortManager = new SortManager();

    OverviewProvider.Instance.on('updated', this.checkStateFromProvider);

    if (OverviewProvider.Instance.hasData()) {
      this.state.totalCount = OverviewProvider.Instance.report.total;
    }

    sortManager.setColumns({
      'id': SortManager.Desc,
      'comment': SortManager.None
    });

    this.sortManager = sortManager;

    this.permissions = Environment.getPermissions();

    if (this.permissions.canApproveComments || this.permissions.canEditComments ||
      this.permissions.canRemoveComments || this.permissions.canReportAsHam ||
      this.permissions.canReportAsSpam || this.permissions.canUnApproveComments) {
      this.canUseBulkActions = true;
    }

    if (AvatarDriverRegistry.hasDriver(Environment.UserPreferences.cp_avatar_driver)) {
      this.avatarDriver = AvatarDriverRegistry.getDriverName(Environment.UserPreferences.cp_avatar_driver);
    } else {
      this.avatarDriver = AvatarDriverRegistry.getDriverName(AvatarDriverRegistry.DefaultDriverName);
    }
  }
};
