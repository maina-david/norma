/**
 * Adapted from https://github.com/hypothesis/client/tree/master/src/annotator/anchoring/types
 */

import {
  fromRange as domAnchorTextPositionFromRange,
  toRange as domAnchorTextPositionToRange,
} from 'dom-anchor-text-position';

export default class TextPositionAnchor {
  constructor(root, start, end) {
    this.root = root;
    this.start = start;
    this.end = end;
  }

  static fromRange(root, range) {
    const selector = domAnchorTextPositionFromRange(root, range);
    return TextPositionAnchor.fromSelector(root, selector);
  }

  static fromSelector(root, selector) {
    return new TextPositionAnchor(root, selector.start, selector.end);
  }

  toSelector() {
    return {
      type: 'TextPositionSelector',
      start: this.start,
      end: this.end,
    };
  }

  toRange() {
    return domAnchorTextPositionToRange(this.root, {
      start: this.start,
      end: this.end,
    });
  }
}
