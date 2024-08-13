import highlighter from '../libryo-highlighter';
import lodash from 'lodash';
import Highlighter from '../highlighter';

const highlighterClass = 'libryo-highlight';
const highlighterIdAttributeName = 'data-highlight-id';


const getRangeTextNode = (node, type = 'firstChild') => {
  if (!node[type]) {
    return node;
  }

  return node[type] && node[type].nodeType === Node.TEXT_NODE
    ? node[type]
    : getRangeTextNode(node[type]);
};

window.lodash = lodash;

export default {
  extractContent (container, startReference, endReference) {
    return Promise.all([
      Highlighter.anchor(container, startReference.selectors),
      new Promise((resolve) => {
        resolve(
          endReference
            ? Highlighter.anchor(container, endReference.selectors)
            : null,
        );
      }),
    ]).then(([start, end]) => {
      if (!end) {
        start.setEndAfter(container.lastChild);
      } else {
        start.setEndBefore(end.startContainer);
      }

      const out = document.createElement('div');

      out.appendChild(start.cloneContents());

      if (out.lastChild && out.lastChild.innerText.trim().length === 0) {
        out.lastChild.remove();
      }

      return out.innerHTML;
    });
  },
  showHighlights (container, references) {
    return Promise.all(references.map((ref, index) => (!ref.selectors || ref.selectors.length < 1
      ? Promise.resolve()
      : Highlighter.anchor(container, ref.selectors)
        .then((range) => {
          const options = { ...highlighter.renderHighlights.optionDefaults };
          options.highlightClass = highlighterClass;
          options.idAttributeName = highlighterIdAttributeName;
          options.cssClasses = [`bg-ref-${ref.type}`, 'bg-transparent', ref.referenceable_type];

          return highlighter.renderHighlights.highlightRange(
            highlighter.anchor.range.sniff(range).normalize(),
            ref.id,
            options,
          ).map((el) => {
            el.setAttribute('data-ref-to', ref.referenceable_id);
            el.setAttribute('data-index', index);

            return el;
          });
        }))));
  },
  generateSelections (commonId, node = null) {
    const body = node || document.querySelector('#toc-content');
    let num = body.querySelector(`[data-inline="num"][data-id="${commonId}"]`);
    let heading = body.querySelector(`[data-inline="heading"][data-id="${commonId}"]`);
    num = num || heading || null;
    heading = heading || num || null;

    const headingFollows = num.compareDocumentPosition(heading) === Node.DOCUMENT_POSITION_FOLLOWING;
    const start = headingFollows ? getRangeTextNode(num) : getRangeTextNode(heading);
    const end = headingFollows ? getRangeTextNode(heading, 'lastChild') : getRangeTextNode(num, 'lastChild');

    const range = document.createRange();
    range.setStart(start, 0);
    range.setEnd(end, end.textContent.length);

    let content = range.commonAncestorContainer;

    if (content.nodeType === Node.TEXT_NODE) {
      content = content.parentElement;
    }

    content = content.outerHTML;

    const typeElement = num.closest('[data-type]') || heading.closest('[data-type]');
    const type = typeElement ? typeElement.getAttribute('data-type') : null;
    const levelElement = num.closest('[data-level]') || heading.closest('[data-level]');
    const level = levelElement ? levelElement.getAttribute('data-level') : null;
    const secElement = num.closest('[data-is_section]') || heading.closest('[data-is_section]');
    const isSection = secElement ? secElement.getAttribute('data-is_section') : false;

    const selectors = highlighter.anchor.htmlAnchoring.describe(
      body,
      highlighter.anchor.range.sniff(range),
      { ignoreClasses: [highlighterClass] },
    );

    const quote = selectors.filter((item) => item.type === 'TextQuoteSelector')[0];
    const position = selectors.filter((item) => item.type === 'TextPositionSelector')[0];

    return {
      id: `li${commonId}`,
      selectors,
      text: quote.exact,
      start: position.start,
      level: level ? parseInt(level, 10) : level,
      norm_type: type,
      content,
      is_section: isSection === '1',
      number: num ? num.textContent : '',
      heading: heading && heading !== num ? heading.textContent : '',
    };
  },
};
