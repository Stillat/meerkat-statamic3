import ThreadContext from '../../Data/Comments/threadContext';
import Comment from '../../Data/Comments/comment';
import Author from '../../Data/Comments/author';
import PagedResponse from './pagedResponse';
import CommentCollection from '../../Data/Comments/commentCollection';
import {PagedMetaData} from '../../Data';
import Type from '../../Types/type';
import {CommentRepository} from '../../Repositories';

class CommentResponse extends PagedResponse {

  constructor() {
    super();

    /** {CommentRepository} */
    this._originator = null;
    this._threadMapping = {};
    this.threads = [];

    this._authorMapping = {};
    this.authors = [];

    this._commentMapping = {};
    this.comments = new CommentCollection();
    this.pages = new PagedMetaData();
    this.sortString = '';
  }

  clear() {
    this._threadMapping = {};
    this.threads = [];
    this._authorMapping = {};
    this.authors = [];
    this._commentMapping = {};
    this.comments = new CommentCollection();
    this.pages = new PagedMetaData();
    this.sortString = '';
  }

  /**
   * Converts an API response to a new CommentResponse object.
   *
   * @param {Object} result The API response.
   * @param {CommentRepository} originator The repository that processed the request.
   * @returns {CommentResponse}
   */
  static fromApiResponse(result, originator: CommentRepository) {
    let response = new CommentResponse();

    response._originator = originator;

    if (result.success) {
      for (let i = 0; i < result.threads.length; i += 1) {
        let newThread = ThreadContext.fromApiObject(result.threads[i]);

        response._threadMapping[newThread.id] = newThread;

        response.threads.push(newThread);
      }

      for (let i = 0; i < result.authors.length; i += 1) {
        let newAuthor = Author.fromApiObject(result.authors[i]);

        response._authorMapping[newAuthor.id] = newAuthor;

        response.authors.push(newAuthor);
      }

      for (let i = 0; i < result.comments.length; i += 1) {
        let newComment = Comment.fromApiObject(result.comments[i]);

        newComment._internalCommentResponse = response;

        response._commentMapping[newComment.id] = newComment;

        response.comments.push(newComment);
      }

      response.pages = PagedMetaData.fromApiObject(result.pages);
      response.sortString = Type.withDefault(result[CommentResponse.ApiSortString], '');
    }

    return response;
  }

  /**
   * Attempts to locate an author with the provided identifier.
   *
   * @param {string} authorId The author's identifier.
   * @returns {Author|null}
   */
  getResponseAuthor(authorId: String): Author {
    return Type.withDefault(this._authorMapping[authorId], null);
  }

  /**
   * Attempts to locate a comment with the provided identifier.
   *
   * @param {string} commentId The comment's identifier.
   * @returns {Comment|null}
   */
  getResponseComment(commentId: String): Comment {
    return Type.withDefault(this._commentMapping[commentId], null);
  }

  /**
   * Attempts to locate a thread with the provided identifier.
   *
   * @param {string} threadId The thread's identifier..
   * @returns {ThreadContext|null}
   */
  getResponseThread(threadId: String): ThreadContext {
    return Type.withDefault(this._threadMapping[threadId], null);
  }

}

CommentResponse.ApiSortString = 'orders';

export default CommentResponse;

