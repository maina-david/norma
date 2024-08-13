import tippy from 'tippy.js';

tippy.setDefaultProps({ delay: 300, animation: 'scale-subtle' });

/* eslint-disable no-param-reassign */
export default {
  install(app) {
    app.directive('tooltip', (el, binding) => {
      if (binding.value.trim() !== '') {
        tippy(el, { content: binding.value, allowHTML: true, placement: 'auto' });
      }
    });
  },
};
