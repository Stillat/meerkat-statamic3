import Environment from '../Config/environment';
import {debounce} from '../utils';

class Addons {

  runActualLinkCheck(packageName, link) {
    let matchingElement = Environment.ContextJquery('td').filter(function () {
      return Environment.ContextJquery(this).text() === packageName;
    });

    if (matchingElement !== null && matchingElement.length === 1) {
      let parentElement = Environment.ContextJquery(matchingElement).parent(),
        alreadyInjected = parentElement.data('addon-listing-updated');

      if (typeof alreadyInjected === 'undefined' || alreadyInjected === null || alreadyInjected.trim().length === '') {
        parentElement.data('addon-listing-updated', 'true');

        parentElement.find('td').each(function () {
          let currentElement = Environment.ContextJquery(this),
            currentContent = currentElement.text(),
            newInnerContent = '<a href="' + link + '">' + currentContent + '</a>';

          currentElement.html(newInnerContent);
        });
      }
    }
  }

  addLinkToPackage(packageName, link) {
    Environment.ContextJquery('body').bind('DOMSubtreeModified', function () {
      debounce(this.runActualLinkCheck(packageName, link), 250);
    }.bind(this));
  }

}

export default Addons;
