/**
 * Aapted from https://github.com/hypothesis/client/tree/master/src/annotator/anchoring/types
 */

import xpathRange from './range';

const missingParameter = (name) => {
  throw new Error(`missing required parameter "${name}"`);
};

export default class RangeAnchor {
  constructor(root, range) {
    if (root == null) {
      missingParameter('root');
    }
    if (range == null) {
      missingParameter('range');
    }
    this.root = root;
    this.range = xpathRange.sniff(range).normalize(this.root);
  }

  static fromRange(root, range) {
    return new RangeAnchor(root, range);
  }

  // Create and anchor using the saved Range selector.
  static fromSelector(root, selector) {
    const data = {
      start: selector.startContainer,
      startOffset: selector.startOffset,
      end: selector.endContainer,
      endOffset: selector.endOffset,
    };
    const range = new xpathRange.SerializedRange(data);
    return new RangeAnchor(root, range);
  }

  toRange() {
    return this.range.toRange();
  }

  toSelector(options) {
    if (options == null) {
      options = {};
    }
    const range = this.range.serialize(this.root, options.ignoreClasses);
    return {
      type: 'RangeSelector',
      startContainer: range.start,
      startOffset: range.startOffset,
      endContainer: range.end,
      endOffset: range.endOffset,
    };
  }
}
