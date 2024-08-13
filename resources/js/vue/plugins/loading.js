const template = `
<div class="loading">
  <div class="effect-1 effects"></div>
  <div class="effect-2 effects"></div>
  <div class="effect-3 effects"></div>
</div>
`;

/* eslint-disable no-param-reassign */
export default {
  install(app) {
    app.directive('loading', (el, binding) => {
      el.classList.add('relative');
      el.classList.add('min-h-12');

      const container = el.querySelector('.loader-container');

      if (container) {
        container.remove();
      }

      if (binding.value) {
        const div = document.createElement('div');
        div.setAttribute(
          'class',
          'loader-container absolute top-0 left-0 h-full w-full flex items-center justify-center',
        );
        div.setAttribute('style', 'background:rgba(159, 162, 167, 0.1)');
        div.innerHTML = template;

        const reference = el.clientWidth > el.clientHeight ? el.clientHeight : el.clientWidth;
        const dimensions = reference > 60 ? 60 : reference * 0.8;
        const loading = div.querySelector('.loading');

        if (loading) {
          loading.setAttribute('style', `width:${dimensions}px;height:${dimensions}px;`);
        }
        el.prepend(div);
      } else {
        el.classList.remove('min-h-12');
      }
    });
  },
};
