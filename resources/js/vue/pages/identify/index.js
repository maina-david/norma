// import 'emoji-picker-element';
import { createApp } from 'vue';
import { debounce } from 'lodash';
import bus from '@/vue/plugins/bus';
import loading from '@/vue/plugins/loading';
import formats from '@/vue/plugins/formats';
import tooltips from '@/vue/plugins/tooltips';
import IdentifyCitationsPage from './IdentifyCitationsPage.vue';

const permissions = [...(window.permissions || [])];

const init = debounce((e) => {
  if (document.querySelector('#app') && !document.querySelector('#app[data-v-app]')) {
    const props = {
      magi: window.magi,
      task: window.task,
      expression: window.expression,
      workReference: window.workReference,
      work: window.work,
      appliedFilters: window.appliedFilters,
      pageMeta: window.pageMeta,
      userId: window.consumer,
      hasNotes: window.hasNotes,
      plainTextSource: window.plainTextSource,
      activate: window.activateReference,
    };

    createApp(IdentifyCitationsPage, props)
      .use(bus)
      .use(loading)
      .use(formats)
      .use(tooltips)
      .provide('can', (query) => {
        const app = query.split('.')[0];

        return permissions.includes(`${app}.superuser`) || permissions.includes(query);
      })
      .mount('#app');
  }
}, 200);

document.addEventListener('turbo:load', (e) => init(e));

document.addEventListener('turbo:frame-load', (e) => init(e));

document.addEventListener('turbo:before-stream-render', (e) => init(e));
