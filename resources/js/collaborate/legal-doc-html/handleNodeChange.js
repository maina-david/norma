import { setIndentByMargin } from './formatActions';

export default (e) => {
  // when a node is changed, check if it's a root level node,
  // and update the data-indent attribute according to the margin-left style
  if (e.element.parentNode.localName === 'body') {
    setIndentByMargin(e.element);
  }
};
