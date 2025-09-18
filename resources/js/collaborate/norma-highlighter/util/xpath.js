/**
 *
 * Adapted from chromium devtools:
 * https://github.com/chromium/chromium/blob/77578ccb4082ae20a9326d9e673225f1189ebb63/third_party/blink/renderer/devtools/front_end/elements/DOMPath.js#L242
 */

/**
 * @unrestricted
 */
const Step = class {
  /**
   * @param {string} value
   * @param {boolean} optimized
   */
  constructor(value, optimized) {
    this.value = value;
    this.optimized = optimized || false;
  }

  /**
   * @override
   * @return {string}
   */
  toString() {
    return this.value;
  }
};

/**
 * @param {!Node} node
 * @return {number}
 */
const xPathIndex = (node) => {
  // Returns -1 in case of error, 0 if no siblings matching the same expression,
  // <XPath index among the same expression-matching sibling nodes> otherwise.
  function areNodesSimilar(left, right) {
    if (left === right) {
      return true;
    }

    if (left.nodeType === Node.ELEMENT_NODE && right.nodeType === Node.ELEMENT_NODE) {
      return left.localName === right.localName;
    }

    if (left.nodeType === right.nodeType) {
      return true;
    }

    // XPath treats CDATA as text nodes.
    const leftType = left.nodeType === Node.CDATA_SECTION_NODE ? Node.TEXT_NODE : left.nodeType;
    const rightType = right.nodeType === Node.CDATA_SECTION_NODE ? Node.TEXT_NODE : right.nodeType;
    return leftType === rightType;
  }

  const siblings = node.parentNode ? node.parentNode.children : null;
  if (!siblings) {
    return 0;
  } // Root node - no siblings.
  let hasSameNamedElements;
  for (let i = 0; i < siblings.length; ++i) {
    if (areNodesSimilar(node, siblings[i]) && siblings[i] !== node) {
      hasSameNamedElements = true;
      break;
    }
  }
  if (!hasSameNamedElements) {
    return 0;
  }
  let ownIndex = 1; // XPath indices start with 1.
  for (let i = 0; i < siblings.length; ++i) {
    if (areNodesSimilar(node, siblings[i])) {
      if (siblings[i] === node) {
        return ownIndex;
      }
      ++ownIndex;
    }
  }
  return -1; // An error occurred: |node| not found in parent's children.
};

const xPathValue = (node, optimized) => {
  let ownValue;
  const ownIndex = xPathIndex(node);
  if (ownIndex === -1) {
    return null;
  } // Error.

  switch (node.nodeType) {
    case Node.ELEMENT_NODE:
      if (optimized && node.getAttribute('id')) {
        return new Step(`//*[@id="${node.getAttribute('id')}"]`, true);
      }
      ownValue = node.localName;
      break;
    case Node.ATTRIBUTE_NODE:
      ownValue = `@${node.nodeName}`;
      break;
    case Node.TEXT_NODE:
    case Node.CDATA_SECTION_NODE:
      ownValue = 'text()';
      break;
    case Node.PROCESSING_INSTRUCTION_NODE:
      ownValue = 'processing-instruction()';
      break;
    case Node.COMMENT_NODE:
      ownValue = 'comment()';
      break;
    case Node.DOCUMENT_NODE:
      ownValue = '';
      break;
    default:
      ownValue = '';
      break;
  }

  if (ownIndex > 0) {
    ownValue += `[${ownIndex}]`;
  }

  return new Step(ownValue, node.nodeType === Node.DOCUMENT_NODE);
};

export default (node, root, optimized = true, ignoreClasses = []) => {
  if (node.nodeType === Node.DOCUMENT_NODE) {
    return '/';
  }

  const steps = [];
  let contextNode = node;
  // find the first parent that is not in the ignore list
  const containsClass = (clsName) => contextNode.parentNode.classList.contains(clsName);
  while (ignoreClasses.some(containsClass)) {
    contextNode = contextNode.parentNode;
  }

  while (!contextNode.isSameNode(root)) {
    const step = xPathValue(contextNode, optimized);
    if (!step) {
      break;
    } // Error - bail out early.
    steps.push(step);
    if (step.optimized) {
      break;
    }
    contextNode = contextNode.parentNode;
  }

  steps.reverse();
  return (steps.length && steps[0].optimized ? '' : '/') + steps.join('/');
};
