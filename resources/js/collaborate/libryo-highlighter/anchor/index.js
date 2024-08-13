import htmlAnchoring from './htmlAnchoring';
import RangeAnchor from './RangeAnchor';
import TextPositionAnchor from './TextPositionAnchor';
import TextQuoteAnchor from './TextQuoteAnchor';
import range from './range';

export default {
  htmlAnchoring,
  RangeAnchor,
  TextPositionAnchor,
  TextQuoteAnchor,
  range: {
    ...range,
  },
};
