/* eslint-disable no-param-reassign */
import cuid from 'cuid';
import Highlighter from '../highlighter';
import getSourceTransformer from '../legal-doc-html/getSourceTransformer';

const allow = [
  'chapter',
  'part',
  'section',
  'article',
  'division',
  'title',
];

export default class Tagger {
  blocks = [
    'address', 'article', 'aside', 'blockquote', 'canvas', 'dd', 'div', 'dl', 'dt', 'fieldset', 'figcaption', 'figure',
    'footer', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'hr', 'li', 'main', 'nav', 'noscript', 'ol', 'p',
    'pre', 'section', 'table', 'tfoot', 'ul', 'video',
  ];

  pairs = [];

  generateSelectors(element, regex) {
    // const text = this.extractText(element);
    const original = element.innerHTML;

    element.innerHTML = element.innerHTML.replace(/display:\s*none;?/gmi, '');

    const text = element.innerText;

    const generated = this.generateTextQuoteSelectors(text, text.matchAll(regex));

    element.innerHTML = original;

    return generated;
  }

  extractText(element) {
    const blocks = new RegExp(`(<(?:${this.blocks.join('|')})[^>]*>)`, 'gim');

    return element.innerHTML
      .replaceAll(blocks, '\n$1')
      .replaceAll(/&nbsp;/g, ' ')
      .replaceAll(/ +/g, ' ')
      .replaceAll(/<\/?\w+[^>]*>/gim, '');
  }

  // eslint-disable-next-line class-methods-use-this
  generateTextPositionSelector(text, match) {
    let firstMatch = match.value[1].trimStart();
    const firstMatchIndex = match.value.index + (match.value[1].length - firstMatch.length);
    let fromStart = text.substr(0, firstMatchIndex + 1);
    let newLines = fromStart.match(/\n/g);
    newLines = newLines ? newLines.length : 0;
    firstMatch = firstMatch.trimEnd();
    let matchLines = firstMatch.trimEnd().match(/\n/g);
    matchLines = matchLines ? matchLines.length : 0;
    matchLines = firstMatch.length + matchLines;

    firstMatch = {
      end: firstMatchIndex + matchLines - newLines,
      start: firstMatchIndex - newLines,
      type: 'TextPositionSelector',
      exact: match.value[1].replace(/\n/g, ''),
    };

    if (!match.value[2] || match.value[2].length < 1) {
      return [firstMatch];
    }

    let secondMatch = match.value[2].trimStart();
    const secondMatchIndex = firstMatch.start + match.value[0].indexOf(secondMatch);
    fromStart = text.substr(0, secondMatch + 1);
    newLines = fromStart.match(/\n/g);
    newLines = newLines ? newLines.length : 0;
    secondMatch = secondMatch.trimEnd();
    matchLines = secondMatch.trimEnd().match(/\n/g);
    matchLines = matchLines ? matchLines.length : 0;
    matchLines = secondMatch.length + matchLines;

    return [
      firstMatch,
      {
        end: secondMatchIndex + matchLines - newLines,
        start: secondMatchIndex - newLines,
        type: 'TextPositionSelector',
        exact: match.value[2].replace(/\n/g, ''),
      },
    ];
  }

  generateTextQuoteSelectors(text, matches) {
    let result = matches.next();
    const selectors = [];

    while (!result.done) {
      selectors.push(this.getSelectorsFromMatch(text, result));
      // selectors.push(this.generateTextPositionSelector(text, result));
      result = matches.next();
    }

    return [...selectors];
  }

  // eslint-disable-next-line class-methods-use-this
  getSelectorsFromMatch(text, match) {
    const subStr = text.substring(match.value.index, match.value.index + match.value[0].length + 1);
    const firstMatchIndex = subStr.indexOf(match.value[1]) + match.value.index;
    const secondMatchIndex = subStr.indexOf(match.value[2]) + match.value.index;
    const second = match.value[2].trimEnd();

    let prefix = text.substring(firstMatchIndex - 31, firstMatchIndex).replace(/\n/g, '');
    let end = firstMatchIndex + match.value[1].length;
    let suffix = text.substring(end, end + 25).replace(/\n/g, '');

    const firstMatch = {
      type: 'TextQuoteSelector',
      exact: match.value[1].replace(/\n/g, ''),
      prefix,
      suffix,
    };

    prefix = text.substring(secondMatchIndex - 24, secondMatchIndex).replace(/\n/g, '');
    end = secondMatchIndex + second.length;
    suffix = text.substring(end, end + 25).replace(/\n/g, '');

    const secondMatch = {
      type: 'TextQuoteSelector',
      exact: second.replace(/\n/g, ''),
      prefix,
      suffix,
    };

    return [firstMatch, secondMatch];
  }

  // eslint-disable-next-line class-methods-use-this
  alreadyTagged(range, attributes) {
    if (!range || !range.startContainer) {
      return false;
    }

    const el = range.startContainer.nodeType === 3 ? range.startContainer.parentElement : range.startContainer;

    const attr = attributes['data-inline'] || 'num';
    const closest = el.closest(`[data-inline="${attr}"]`);

    return !!closest && closest.textContent === range.cloneContents().textContent;
  }

  // eslint-disable-next-line class-methods-use-this,no-unused-vars
  wrapWithTag(element, selector, attributes = {}, tries = 0) {
    if (!selector) {
      return Promise.resolve(null);
    }

    return (new Promise((res) => setTimeout(res(true), 10)))
      .then(() => Highlighter.anchor(element, [selector]))
      .then((range) => {
        if (!range || range.cloneContents().textContent.trim().length < 1 || this.alreadyTagged(range, attributes)) {
          return null;
        }

        const span = this.createSpan(attributes);
        const newRange = this.findCommonRangeElements(range);

        // eslint-disable-next-line no-param-reassign
        range = newRange.cloneContents().textContent.trim().length < 1 ? range : newRange;

        // console.log(selector, range);

        if (range.startContainer.isSameNode(range.endContainer)) {
          range.surroundContents(span);

          return new Promise((res) => setTimeout(() => res(range), 10));
        }

        try {
          range.surroundContents(span);
        } catch (e) {
          // console.log('missed', selector);
          if (tries < 1) {
            return (new Promise((res) => setTimeout(res(true), 10)))
              .then(() => this.wrapWithTag(element, selector, attributes, tries + 1));
          }
        }

        return range;
      })
      .catch(() => {
        // console.log(e, selector.exact);
      });
  }

  // eslint-disable-next-line class-methods-use-this
  findNextTextSibling(element) {
    while (!element.nextSibling) {
      // eslint-disable-next-line no-param-reassign
      element = element.parentElement;
    }

    // eslint-disable-next-line no-param-reassign
    element = element.nextSibling;

    while (element.firstChild) {
      // eslint-disable-next-line no-param-reassign
      element = element.firstChild;
    }

    return element;
  }

  // eslint-disable-next-line class-methods-use-this
  findCommonRangeElements(range) {
    const newRange = range.cloneRange();

    if (newRange.startContainer.isSameNode(newRange.endContainer)) {
      return newRange;
    }

    if (newRange.endOffset === 0) {
      const nextSibling = this.findNextTextSibling(newRange.startContainer);
      if (nextSibling.isSameNode(newRange.endContainer)) {
        newRange.setEnd(newRange.startContainer, newRange.cloneContents().textContent.length);
      }
    }

    return newRange;
  }

  applySourceRegEx(element, source) {
    let levels = getSourceTransformer(source, '').getLevels();
    levels = [...levels];
    levels = levels.filter((item) => allow.includes(item.level));
    levels = levels.map((level, index) => {
      let regex = level.regex.toString()
        .replace(/^\//, '')
        .replace(/\/[a-z]*$/, level.hasHeading ? '(.*)' : '');

      regex = new RegExp(regex, 'gim');

      return this.tagElement(element, level.level, index, level.level.includes('section'), regex);
    });

    return Promise.all(levels);
  }

  tagElement(element, type, level, isSection, regex, nextTick) {
    this.pairs = this.generateSelectors(element, regex).reverse();

    return this.recursivelyTagElement(element, type, level, isSection, nextTick);
  }

  // eslint-disable-next-line class-methods-use-this
  createSpan(attributes) {
    const span = document.createElement('span');
    Object.keys(attributes).forEach((attr) => {
      span.setAttribute(attr, attributes[attr]);
    });

    return span;
  }

  recursivelyTagElement(element, type, level, isSection, nextTick) {
    const attr = { 'data-id': cuid.slug() };
    const firstAttrs = { 'data-type': type, 'data-inline': 'num', 'data-level': level };
    if (isSection) {
      firstAttrs['data-is_section'] = 1;
    }
    const pair = this.pairs.shift();

    if (!pair) {
      return Promise.resolve(true);
    }

    return Promise.resolve()
      .then(() => nextTick())
      .then(() => {
        if (!pair[1]) {
          return false;
        }

        return this.wrapWithTag(element, pair[1], { ...attr, ...(pair[0] ? {} : firstAttrs), 'data-inline': 'heading' });
      })
      .then(() => nextTick())
      .then(() => {
        if (!pair[0]) {
          return false;
        }

        return this.wrapWithTag(element, pair[0], { ...attr, ...firstAttrs });
      })
      .then(() => nextTick())
      .then(() => this.recursivelyTagElement(element, type, level, isSection, nextTick));
  }
}
