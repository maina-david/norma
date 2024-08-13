import '@hotwired/turbo';
import { debounce } from 'lodash';

Turbo.setProgressBarDelay(1200);

const initAll = debounce((node) => {
//the html is not actually loaded when turbo:frame-load is triggered... slight delay helps
  window.setTimeout(() => {
    window.findAndInitSelects();
    window.initDayJs(node);
    window.initTiny();
    window.initTippy(node);
    window.initSortable();
    window.initAnnotations();
    window.initLiveChat();
    window.initFilepond();
    window.initLazyContent();
  }, 100);
}, 500);

document.addEventListener('turbo:load', () => {
  initAll(document);
});

document.addEventListener('turbo:frame-load', (e) => {
  initAll(e.target);
});

document.addEventListener('turbo:before-stream-render', (e) => {
  initAll(document);
});

document.addEventListener('turbo:submit-end', (e) => {
  initAll(document);
});
