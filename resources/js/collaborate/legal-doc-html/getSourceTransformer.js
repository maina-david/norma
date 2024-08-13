import { find as _find } from 'lodash';
import { Transformers } from 'legal-doc-html';

export default (source, text) => {
  const TransformerClass = _find(Transformers, {
    transformerName: source,
  });
  return new TransformerClass(text);
};
