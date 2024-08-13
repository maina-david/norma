/**
 * Extracted from https://github.com/hypothesis/client/tree/master/src/annotator/anchoring
 */

import RangeAnchor from './RangeAnchor';
import TextPositionAnchor from './TextPositionAnchor';
import TextQuoteAnchor from './TextQuoteAnchor';

const querySelector = (type, root, selector, options) => {
  const doQuery = (resolve, reject) => {
    try {
      const anchor = type.fromSelector(root, selector, options);
      const range = anchor.toRange(options);
      return resolve(range);
    } catch (error) {
      return reject(error);
    }
  };
  return new Promise(doQuery);
};

export const anchor = (root, selectors = [], options = {}) => {
  let position = null;
  let quote = null;
  let range = null;

  // Collect all the selectors
  selectors.forEach((selector) => {
    switch (selector.type) {
      case 'TextPositionSelector':
        position = selector;
        options.hint = position.start; // TextQuoteAnchor hint
        break;
      case 'TextQuoteSelector':
        quote = selector;
        break;
      case 'RangeSelector':
        range = selector;
        break;
      default:
        break;
    }
  });

  //  // Assert the quote matches the stored quote, if applicable
  //  const maybeAssertQuote = (rnge, anchr) => {
  //    const browserRange = anchr.toRange();
  //    const txt = browserRange.extractContents().textContent;
  //    if ((quote != null ? quote.exact : undefined) != null && txt !== quote.exact) {
  //      throw new Error('quote mismatch');
  //    } else {
  //      return rnge;
  //    }
  //  };
  const maybeAssertQuote = (rnge) => {
    if (
      (typeof quote !== 'undefined' && quote !== null ? quote.exact : undefined) != null &&
      rnge.toString() !== quote.exact
    ) {
      throw new Error('quote mismatch');
    } else {
      return rnge;
    }
  };
  // From a default of failure, we build up catch clauses to try selectors in
  // order, from simple to complex.
  let promise = Promise.reject('unable to anchor');

  if (range != null) {
    promise = promise.catch(() =>
      querySelector(RangeAnchor, root, range, options).then(maybeAssertQuote),
    );
  }

  if (position != null) {
    promise = promise.catch(() =>
      querySelector(TextPositionAnchor, root, position, options).then(maybeAssertQuote),
    );
  }

  if (quote != null) {
    promise = promise.catch(() =>
      // Note: similarity of the quote is implied.
      querySelector(TextQuoteAnchor, root, quote, options),
    );
  }

  return promise;
};

export const describe = (root, range, options) => {
  if (options == null) {
    options = {};
  }
  const types = [RangeAnchor, TextPositionAnchor, TextQuoteAnchor];

  const selectors = (() => {
    const result = [];
    types.forEach((type) => {
      try {
        let selector;
        const anchr = type.fromRange(root, range, options);
        result.push((selector = anchr.toSelector(options)));
      } catch (error) {
        console.log(error);
      }
    });
    return result;
  })();

  return selectors;
};

export default {
  anchor,
  describe,
};
