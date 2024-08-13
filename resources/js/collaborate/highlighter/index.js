/* eslint-disable no-continue */
import {anchor} from './anchoring/html';

export default class Highlighter {
  /**
  * Anchor a set of selectors.
  *
  * This function converts a set of selectors into a document range.
  * It encapsulates the core anchoring algorithm, using the selectors alone or
  * in combination to establish the best anchor within the document.
  *
  * @param {Element} root - The root element of the anchoring context.
  * @param {Selector[]} selectors - The selectors to try.
  * @param {Object} [options]
  * @param {number} [options.hint]
  * @return {Promise<Range>}
  */
  static anchor(root, selectors, options = {}) {
    return anchor(root, selectors, options);
  }
}
