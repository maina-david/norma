import { RangeAnchor, TextPositionAnchor, TextQuoteAnchor } from './types';

/**
 * @typedef {import('./api').Selector} Selector
 */

/**
 * @param {RangeAnchor|TextPositionAnchor|TextQuoteAnchor} anchor
 * @param {Object} [options]
 *  @param {number} [options.hint]
 */
async function querySelector(anc, options = {}) {
  return anc.toRange(options);
}

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
 *   @param {number} [options.hint]
 */
export function anchor(root, selectors, options = {}) {
  let position = null;
  let quote = null;
  let range = null;

  const { length } = selectors;

  // Collect all the selectors
  // eslint-disable-next-line no-plusplus
  for (let i = 0; i < length; i++) {
    const selector = selectors[i];

    switch (selector.type) {
      case 'TextPositionSelector':
        position = selector;
        // eslint-disable-next-line no-param-reassign
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
  }

  /**
   * Assert the quote matches the stored quote, if applicable
   * @param {Range} rng
   */
  const maybeAssertQuote = (rng) => {
    if (quote?.exact && rng.toString() !== quote.exact) {
      throw new Error('quote mismatch');
    } else {
      return rng;
    }
  };

  // From a default of failure, we build up catch clauses to try selectors in
  // order, from simple to complex.
  /** @type {Promise<Range>} */
  // eslint-disable-next-line prefer-promise-reject-errors
  let promise = Promise.reject('unable to anchor');

  if (range) {
    promise = promise.catch(() => {
      const anc = RangeAnchor.fromSelector(root, range);
      return querySelector(anc, options).then(maybeAssertQuote);
    });
  }

  if (position) {
    promise = promise.catch(() => {
      const anc = TextPositionAnchor.fromSelector(root, position);
      return querySelector(anc, options).then(maybeAssertQuote);
    });
  }

  if (quote) {
    promise = promise.catch(() => {
      const anc = TextQuoteAnchor.fromSelector(root, quote);
      return querySelector(anc, options);
    });
  }

  return promise;
}

/**
 * @param {Element} root
 * @param {Range} range
 */
export function describe(root, range) {
  const types = [RangeAnchor, TextPositionAnchor, TextQuoteAnchor];
  const result = [];
  const { length } = types;

  // eslint-disable-next-line no-plusplus
  for (let i = 0; i < length; i++) {
    const type = types[i];
    try {
      const anc = type.fromRange(root, range);
      result.push(anc.toSelector());
    } catch (error) {
      //
    }
  }

  return result;
}
