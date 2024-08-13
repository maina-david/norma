import textNodes from './textNodes';
import getSelection from '../selection/getSelection';
import xpath from './xpath';

export const flatten = (array) => {
  const flattn = (ary) => {
    let flat = [];

    ary.forEach((el) => {
      flat = flat.concat(el && Array.isArray(el) ? flattn(el) : el);
    });

    return flat;
  };

  return flattn(array);
};

export const contains = (parent, child) => {
  let node = child;
  while (node != null) {
    if (node === parent) {
      return true;
    }
    node = node.parentNode;
  }
  return false;
};

export const getTextNodes = textNodes;

export const getLastTextNodeUpTo = (n) => {
  switch (n.nodeType) {
    case Node.TEXT_NODE:
      return n; // We have found our text node.
      break;
    case Node.ELEMENT_NODE:
      // This is an element, we need to dig in
      if (n.lastChild != null) {
        // Does it have children at all?
        const result = getLastTextNodeUpTo(n.lastChild);
        if (result != null) {
          return result;
        }
      }
      break;
    default:
  }
  // Not a text node, and not an element node.
  // Could not find a text node in current node, go backwards
  n = n.previousSibling;
  if (n != null) {
    return getLastTextNodeUpTo(n);
  }
  return null;
};

// Public: determine the first text node in or after the given jQuery node.
export const getFirstTextNodeNotBefore = (n) => {
  switch (n.nodeType) {
    case Node.TEXT_NODE:
      return n; // We have found our text node.
      break;
    case Node.ELEMENT_NODE:
      // This is an element, we need to dig in
      if (n.firstChild != null) {
        // Does it have children at all?
        const result = getFirstTextNodeNotBefore(n.firstChild);
        if (result != null) {
          return result;
        }
      }
      break;
    default:
  }
  // Not a text or an element node.
  // Could not find a text node in current node, go forward
  n = n.nextSibling;
  if (n != null) {
    return getFirstTextNodeNotBefore(n);
  }
  return null;
};

export const readRangeViaSelection = (range) => {
  const sel = getSelection(); // Get the browser selection object
  sel.removeAllRanges(); // clear the selection
  sel.addRange(range.toRange()); // Select the range
  return sel.toString(); // Read out the selection
};

// Get the node name for use in generating an xpath expression.
const getNodeName = (node) => {
  const nodeName = node.nodeName.toLowerCase();
  switch (nodeName) {
    case '#text':
      return 'text()';
    case '#comment':
      return 'comment()';
    case '#cdata-section':
      return 'cdata-section()';
    default:
      return nodeName;
  }
};

const findChild = (node, type, index) => {
  if (!node.hasChildNodes()) {
    throw new Error('XPath error: node has no children!');
  }
  const children = node.childNodes;
  let found = 0;
  let foundChild = null;
  children.forEach((child) => {
    const name = getNodeName(child);
    if (name === type) {
      found += 1;
      if (found === index) {
        foundChild = child;
      }
    }
  });
  if (!foundChild) {
    throw new Error('XPath error: wanted child not found.');
  }
  return foundChild;
};

export const xpathFromNode = xpath;

export const nodeFromXPath = (xp, root) => {
  const steps = xp.substring(1).split('/');
  let node = root;
  steps.forEach((step) => {
    // eslint-disable-next-line
    let [name, idx] = Array.from(step.split('['));
    idx = idx != null ? parseInt((idx != null ? idx.split(']') : undefined)[0], 10) : 1;
    node = findChild(node, name.toLowerCase(), idx);
  });

  return node;
};

export const escape = (html) => {
  html
    .replace(/&(?!\w+;)/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
  return html;
};

export const maxZIndex = (elements) => {
  const all = elements.map((el) => {
    return el.style.position === 'static'
      ? -1
      : // Use parseFloat since we may get scientific notation for large
        // values.
        parseFloat($(el).css('z-index')) || -1;
  });
  // eslint-disable-next-line
  return Math.max.apply(Math, all);
};

export default {
  flatten,
  contains,
  getTextNodes,
  getLastTextNodeUpTo,
  getFirstTextNodeNotBefore,
  readRangeViaSelection,
  getNodeName,
  findChild,
  xpathFromNode,
  nodeFromXPath,
  escape,
  maxZIndex,
};
