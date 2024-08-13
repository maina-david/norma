import Highlighter from '@/collaborate/highlighter';

export default (contentElement) => {
  function handleScroll(selectors, tries = 0) {
    if (tries > 5) {
      return Promise.resolve(false);
    }
    const onFail = () => {
      return new Promise((res) => setTimeout(() => handleScroll(selectors, tries + 1).then((e) => res(e)), 1000));
    };

    return Highlighter.anchor(contentElement.value, selectors)
      .then((range) => {
        if (range) {
          const start = range.startContainer.nodeType === Node.TEXT_NODE
            ? range.startContainer.parentElement
            : range.startContainer;

          start.scrollIntoView();

          return true;
        }
        return onFail();
      })
      .catch(() => onFail());
  }
  function scrollToSelection(selectors) {
    if (!contentElement.value) {
      return Promise.resolve();
    }

    return handleScroll(selectors);
  }

  return { scrollToSelection };
};
