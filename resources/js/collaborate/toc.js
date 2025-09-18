import {debounce} from "lodash";
import {createApp} from 'vue';
import App from './App.vue';
import bus from "../plugins/vue/bus";
import loading from "../plugins/vue/loading";
import store from './store';
import NormaIcon from "./components/NormaIcon.vue";
import Pagination from "./components/Pagination.vue";
import '../../scss/toc.scss';

const init = debounce(() => {
  if (document.querySelector('#app') && !document.querySelector('#app[data-v-app]')) {
    createApp(App)
        .use(bus)
        .use(loading)
        .use(store)
        .component('NormaIcon', NormaIcon)
        .component('Pagination', Pagination)
        .mount('#app');
  }
}, 1500)

init();

document.addEventListener('turbo:load', () => init());

document.addEventListener('turbo:frame-load', () => init());

document.addEventListener('turbo:before-stream-render', () => init());
