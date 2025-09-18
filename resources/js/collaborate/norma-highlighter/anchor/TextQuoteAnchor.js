/**
 * Adapted from https://github.com/hypothesis/client/tree/master/src/annotator/anchoring/types
 */

import {
  fromRange as domAnchorTextQuoteRromRange,
  toRange as domAnchorTextQuoteToRange,
  toTextPosition as domAnchorTextQuoteToTextPosition,
} from 'dom-anchor-text-quote';
import TextPositionAnchor from './TextPositionAnchor';

export default class TextQuoteAnchor {
  constructor(root, exact, context) {
    if (context == null) {
      context = {};
    }
    this.root = root;
    this.exact = exact;
    this.context = context;
  }

  static fromRange(root, range, options) {
    const selector = domAnchorTextQuoteRromRange(root, range, options);
    return TextQuoteAnchor.fromSelector(root, selector);
  }

  static fromSelector(root, selector) {
    const { prefix, suffix } = selector;
    return new TextQuoteAnchor(root, selector.exact, { prefix, suffix });
  }

  toSelector() {
    return {
      type: 'TextQuoteSelector',
      exact: this.exact,
      prefix: this.context.prefix,
      suffix: this.context.suffix,
    };
  }

  toRange(options) {
    if (options == null) {
      options = {};
    }
    const range = domAnchorTextQuoteToRange(this.root, this.toSelector(), options);
    if (range === null) {
      throw new Error('Quote not found');
    }
    return range;
  }

  toPositionAnchor(options) {
    if (options == null) {
      options = {};
    }
    const anchor = domAnchorTextQuoteToTextPosition(this.root, this.toSelector(), options);
    if (anchor === null) {
      throw new Error('Quote not found');
    }
    return new TextPositionAnchor(this.root, anchor.start, anchor.end);
  }
}
