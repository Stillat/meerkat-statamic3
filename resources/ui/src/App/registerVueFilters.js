import String from '../Types/string';
import {parseMarkdown} from './markdown';

const FilterPrefix = 'meerkat';

/**
 * Registers a single VueJS filter.
 * @param {Object} vue The current VueJS instance.
 * @param {string} filter The filter name.
 * @param {Function} callback The filter implementation.
 */
function registerFilter(vue, filter, callback) {
  vue.filter(FilterPrefix + String.ucFirst(filter), callback);
}

/**
 *  Registers all Meerkat-related VueJS filters.
 *
 * @param {Object} vue The current VueJS instance.
 */
export function registerVueFilters(vue) {
  registerFilter(vue, 'markdown', parseMarkdown);
  registerFilter(vue, 'truncate', String.truncate);
}
