import iziToast from "izitoast";

const defaults = {
  transitionIn: 'fadeInLeft',
  transitionOut: 'fadeOutRight',
  layout: 2,
  position: 'topRight',
  progressBarColor: '#ffffff',
};

const light = {
  ...defaults, iconColor: '#ffffff', color: '#f98080', titleColor: '#ffffff', messageColor: '#ffffff',
};

const dark = {
  ...defaults,
};

window.toast = {
  error: (payload) => iziToast.show({ ...light, ...payload }),
  success: (payload) => iziToast.show({
    color: 'green', iconColor: '#000', ...dark, ...payload,
  }),
  warning: (payload) => iziToast.show({
    color: 'yellow', ...dark, ...payload,
  }),
  info: (payload) => iziToast.show({
    color: 'blue', ...dark, ...payload,
  }),
};

document.addEventListener('turbo:load', (e)=> {
  const observer = new MutationObserver(() => {
    const el = document.querySelector('#global-alert span');
    if (!el) {
      return;
    }
    const type = el.getAttribute('data-type');
    const message = el.textContent;
    if (!type || !message) {
      return;
    }
    window.toast[type]({ message });
  });
  const node = document.querySelector('turbo-frame#global-alert');
  if (!node) {
    return;
  }
  //listen to any of the turbo-frame 's direct children changing - it means that the actions have been performed
  observer.observe(node, { childList: true });
});
