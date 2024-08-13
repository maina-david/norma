import { debounce } from 'lodash';
import { defineCustomElement } from 'vue';
import Pagination from '@/collaborate/components/Pagination.vue';
import WorkSelector from '@/vue/pages/annotations/components/WorkSelector.vue';
import CatalogueDocSelector from '@/vue/pages/annotations/components/CatalogueDocSelector.vue';
import loading from '@/vue/plugins/loading';
import tooltips from '@/vue/plugins/tooltips';
import styles from '@/../css/app.css';

const directives = {
  loading,
  tooltips,
};

const components = {
  // LibryoIcon,
  CatalogueDocSelector,
  VuePagination: Pagination,
  WorkSelector,
};

const kebabCase = (string) => string
  .replace(/([a-z])([A-Z])/g, '$1-$2')
  .replace(/[\s_]+/g, '-')
  .toLowerCase();

const init = debounce(() => {
  Object.keys(components).forEach((key) => {
    const defined = defineCustomElement({ ...components[key], directives, styles: [styles] });
    if (!customElements.get(kebabCase(key))) {
      customElements.define(kebabCase(key), defined);
    }
  });

  // if (document.querySelector('#sharable-app') && !document.querySelector('#sharable-app[data-v-app]')) {
  //   const app = createApp({})
  //     .use(bus)
  //     .use(loading)
  //     .use(store);
  //
  //   Object.keys(components).forEach((key) => {
  //     app.component(key, components[key]);
  //   });
  //
  //   app.mount('#sharable-app');
  // }
}, 1500);

init();

document.addEventListener('turbo:load', () => init());

document.addEventListener('turbo:frame-load', () => init());

document.addEventListener('turbo:before-stream-render', () => init());
