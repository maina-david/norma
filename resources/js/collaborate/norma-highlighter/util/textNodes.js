export default (container) => {
  const nodes = [];
  const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT);
  while (walker.nextNode()) {
    nodes.push(walker.currentNode);
  }
  return nodes;
};
