/* eslint-disable no-param-reassign */
import getSourceTransformer from './getSourceTransformer';

export default (plugin, args, selectedSource) => {
  if (!selectedSource) {
    return args.content;
  }
  const processor = getSourceTransformer(selectedSource, args.content);

  args.content = processor.transformInputHTML(args.content);
  return args.content;
};
