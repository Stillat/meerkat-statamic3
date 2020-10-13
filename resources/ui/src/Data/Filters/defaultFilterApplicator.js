import {AllFilter, IsSpamFilter, PendingFilter, PublishedFilter} from './defaultFilters';
import Type from '../../Types/type';

export function getDefaultFilter(filterName) {
  // Create a temp mapping for all of the default filters.
  let allFilter = new AllFilter(),
    spamFilter = new IsSpamFilter(),
    pendingFilter = new PendingFilter(),
    publishedFilter = new PublishedFilter(),
    filterMap = {};

  filterMap[allFilter.internalName] = allFilter;
  filterMap[spamFilter.internalName] = spamFilter;
  filterMap[pendingFilter.internalName] = pendingFilter;
  filterMap[publishedFilter.internalName] = publishedFilter;

  if (Type.hasValue(filterMap[filterName])) {
    return filterMap[filterName];
  }

  return null;
}
