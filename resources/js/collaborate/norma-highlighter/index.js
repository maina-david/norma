import { registerSelectionListener, deregisterSelectionListener } from './selectionListener';
import getSelection from './selection/getSelection';
import textNodes from './util/textNodes';
import anchor from './anchor';
import renderHighlights from './renderHighlights';

export default {
  deregisterSelectionListener,
  getSelection,
  registerSelectionListener,
  textNodes,
  anchor: {
    ...anchor,
  },
  renderHighlights: {
    ...renderHighlights,
  },
};
