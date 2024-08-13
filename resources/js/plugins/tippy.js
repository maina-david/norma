import tippy from 'tippy.js';

tippy.setDefaultProps({ delay: 300, animation: 'scale-subtle', placement: 'auto' });

window.tippy = tippy;

window.initTippy = (n) => {
  const css = '.tippy:not([aria-describedby|="tippy"]';

  tippy(n && n.querySelectorAll ? n.querySelectorAll(css) : css);
};
