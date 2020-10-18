/** Registers the syncjs hubs that are used across the app implementation. */
import Type from '../Types/type';
import Comment from '../Data/Comments/comment';

/** Create some syncjs hubs that we will use throughout. */
const syncjs = require('syncjs');

export function registerHubs() {
  syncjs.Hubs.make('comments', Type.typeOf(Comment));
  syncjs.Hubs.make('config', Type.typeOf({}));
}
