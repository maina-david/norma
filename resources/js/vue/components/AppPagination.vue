<script setup>
import { uniq } from 'lodash';
import { computed } from 'vue';

const props = defineProps({
  current: { type: Number, required: true },
  perPage: { type: Number, required: true },
  sizeChanger: { type: Boolean, default: false },
  lastPage: { type: Number, required: true },
});
const emit = defineEmits(['page', 'perPage']);

const toFirst = computed(() => props.current - 1);
const toLast = computed(() => props.lastPage - props.current);
const firstTwo = computed(()=> props.lastPage < 2 ? [1] : [1, 2]);
const lastTwo = computed(() => {
  const last = props.lastPage === 1 ? 1 : props.lastPage - 1;

  return props.lastPage < 2 ? [1] : [last, props.lastPage];
});

const pages = computed(() => {
  const pages = [...firstTwo.value];
  let first = toFirst.value > 1 ? props.current - 1 : 1;
  const addToLast = (props.current - first < 1 ? 1 - (props.current - first) : 0) + 2;
  let last = props.current + addToLast > props.lastPage ? props.lastPage : props.current + addToLast;
  last = last > props.lastPage ? props.lastPage : last;
  first -= props.lastPage - props.current < 1 ? 2 : 0;
  first = first < 1 ? 1 : first;

  if (first > 3) {
    pages.push('...');
  }

  for (first; first <= last; first += 1) {
    pages.push(first);
  }

  if (props.lastPage - last > 2) {
    pages.push('...');
  }

  return uniq([...pages, ...lastTwo.value]);
});

function changePage(page) {
  if (page !== '...' && page !== props.current) {
    emit('page', page);
  }
}
function changePageSize({ target }) {
  emit('perPage', +Array.from(target.options).filter((item) => item.selected)[0].value);
}
</script>

<template>
  <div class="pt-2 pb-1 px-4 flex justify-end flex-shrink-0 bg-white app-pagination">
    <div v-if="sizeChanger" class="mr-2">
      <select class="form-select form-select-sm mr-2" @change="changePageSize">
        <option value="20" :selected="perPage === 20">
          20
        </option>
        <option value="40" :selected="perPage === 40">
          40
        </option>
        <option value="60" :selected="perPage === 60">
          60
        </option>
        <option value="80" :selected="perPage === 80">
          80
        </option>
        <option value="100" :selected="perPage === 100">
          100
        </option>
        <option value="150" :selected="perPage === 150">
          150
        </option>
        <option value="200" :selected="perPage === 200">
          200
        </option>
      </select>
    </div>
    <ul v-if="lastPage > 1" class="pagination flex items-center">
      <li class="page-item">
        <a class="page-link lh-sm py-1" href="#" aria-label="Previous" @click.stop.prevent="() => changePage(1)">
          <span aria-hidden="true">&laquo;</span>
        </a>
      </li>
      <li v-for="page in pages" :key="page" class="page-item">
        <a class="page-link lh-sm py-1" :class="{ active: page === current }" href="#" @click.stop.prevent="() => changePage(page)">{{ page }}</a>
      </li>
      <li class="page-item">
        <a class="page-link lh-sm py-1" href="#" aria-label="Next" @click.stop.prevent="() => changePage(lastPage)">
          <span aria-hidden="true">&raquo;</span>
        </a>
      </li>
    </ul>
  </div>
</template>

<style lang="scss">
.app-pagination {
  .page-item {
    @apply flex;
  }

  .page-item:first-child .page-link {
    @apply rounded-l;
  }

  .page-item:last-child .page-link {
    @apply rounded-r border-r;
  }

  .page-link.active,
  .page-link.active:hover {
    @apply bg-primary border-primary;
    color: #fff !important;
    cursor: pointer;
  }

  .pagination {
    margin-bottom: 0;
  }

  .page-link {
    @apply px-4 py-2 border-t border-b border-l border-gray-200;
  }
}
</style>
