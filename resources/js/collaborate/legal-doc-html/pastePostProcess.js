import getSourceTransformer from './getSourceTransformer';

const transformFromSource = (args, selectedSource) => {
  if (!selectedSource) {
    return args.content;
  }
  const processor = getSourceTransformer(selectedSource, args.content);

  return processor.applyTransformToNodeList(args.node.childNodes);
};

export default (plugin, args, selectedSource) => {
  transformFromSource(args, selectedSource);

  // replaces all h-tags with p tags
  args.node.querySelectorAll('h1,h2,h3,h4,h5,h6').forEach((hTag) => {
    const p = document.createElement('p');
    p.innerHTML = hTag.innerHTML;
    hTag.parentNode.replaceChild(p, hTag);
  });

  // bit of a hack to force the TinyMCE cleanup to take place after pasting,
  // so empty spans are removed etc....
  window.setTimeout(() => {
    args.target.setContent(args.target.getContent());
  }, 1000);
};
