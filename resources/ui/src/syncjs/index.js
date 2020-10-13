import Environment from './environment';

export * from './Messaging';
export {reactsToType} from './Messaging/typeNamespaceReactors';

/** Attempts to automatically configure the jQuery instance from the window object. */
if (typeof window['jQuery'] !== 'undefined' && window['jQuery'] !== null) {
  Environment.ContextJquery = window['jQuery'];
}
