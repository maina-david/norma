// import intersect from '@alpinejs/intersect';
import { debounce } from 'lodash';
import { Alpine, Livewire } from '../../../vendor/livewire/livewire/dist/livewire.esm';

import store from '../app/store';

// Alpine.plugin(intersect);
// Alpine.plugin(persist);
// Alpine.plugin(morph);

Object.keys(store).forEach((key) => Alpine.store(key, store[key]));

const initialiseItems = debounce(() => {
  window.findAndInitSelects();
  window.initTiny();
}, 200);

document.addEventListener('livewire:init', () => {
  Livewire.hook('component.init', () => initialiseItems());
  Livewire.hook('element.init', () => initialiseItems());
  Livewire.hook('morph.updated', () => initialiseItems());
  Livewire.hook('morph.added', () => initialiseItems());
});

Livewire.start();

Livewire.directive('tooltip', ({ el, directive }) => {
  let content =  directive.expression;

  window.tippy(el, { content });
});
