import cuid from 'cuid';
import getSourceTransformer from './getSourceTransformer';

const allow = [
  'chapter',
  'part',
  'section',
  'article',
  'division',
  'title',
];

const extractChildren = (node) => {
  let children = [];

  node.children.forEach((child) => {
    if (child.children.length === 0) {
      children.push(child);
      return;
    }

    children = [...children, ...extractChildren(child)];
  });

  return children;
};

const identifyLevels = (nodes, levels) => {
  const currentStructure = [];

  nodes.forEach((node) => {
    // eslint-disable-next-line no-param-reassign
    levels.some((level) => {
      const isMatched = node.parentElement.hasAttribute('data-inline')
        || node.parentElement.hasAttribute('data-type')
        || node.hasAttribute('data-inline')
        || node.hasAttribute('data-type');

      const matches = node.textContent.match(level.regex);

      if (isMatched || !allow.includes(level.level) || !matches) {
        return false;
      }

      const index = currentStructure.indexOf(level.level);

      if (index !== -1) {
        currentStructure.splice(index, currentStructure.length);
      }

      currentStructure.push(level.level);

      let reg = level.regex.toString()
        .replace(/^\//, '')
        .replace(/\/[a-z]*$/, level.hasHeading ? '(.*)' : '');

      reg = new RegExp(reg, 'gim');

      const headingMatch = level.hasHeading && !!node.textContent.match(reg);
      const id = cuid.slug();
      const dataLevel = currentStructure.indexOf(level.level) + 1;

      const numReplacement = `<span data-inline="num" data-id="${id}" data-type="${level.level}" data-level="${dataLevel}">$1</span>`;
      // eslint-disable-next-line no-param-reassign
      node.innerHTML = node.innerHTML.replace(
        headingMatch ? reg : level.regex,
        headingMatch ? `${numReplacement}<span data-id="${id}" data-inline="heading">$2</span>` : numReplacement,
      );

      return true;
    });
  });
};

export default (source, parentNode) => {
  const nodes = extractChildren(parentNode);
  const levels = getSourceTransformer(source, '').getLevels();

  identifyLevels(nodes, levels);

  return parentNode.innerHTML;
};
