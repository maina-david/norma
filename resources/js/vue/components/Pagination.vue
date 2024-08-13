<script>
import { uniq } from 'lodash';

export default {
  name: 'Pagination',
  props: {
    current: { type: Number, required: true },
    perPage: { type: Number, required: true },
    sizeChanger: { type: Boolean, default: false },
    lastPage: { type: Number, required: true },
  },
  emits: ['page', 'perPage'],
  computed: {
    toFirst() {
      return this.current - 1;
    },
    toLast() {
      return this.lastPage - this.current;
    },
    lastTwo() {
      const last = this.lastPage === 1 ? 1 : this.lastPage - 1;

      return this.lastPage < 2 ? [1] : [last, this.lastPage];
    },
    firstTwo() {
      return this.lastPage < 2 ? [1] : [1, 2];
    },
    pages() {
      const pages = [...this.firstTwo];
      let first = this.toFirst > 1 ? this.current - 1 : 1;
      const addToLast = (this.current - first < 1 ? 1 - (this.current - first) : 0) + 2;
      let last = this.current + addToLast > this.lastPage ? this.lastPage : this.current + addToLast;
      last = last > this.lastPage ? this.lastPage : last;
      first -= this.lastPage - this.current < 1 ? 2 : 0;
      first = first < 1 ? 1 : first;

      if (first > 3) {
        pages.push('...');
      }

      for (first; first <= last; first += 1) {
        pages.push(first);
      }

      if (this.lastPage - last > 2) {
        pages.push('...');
      }

      return uniq([...pages, ...this.lastTwo]);
    },
  },
  methods: {
    changePage(page) {
      if (page !== '...' && page !== this.current) {
        this.$emit('page', page);
      }
    },
    changePageSize({ target }) {
      this.$emit('perPage', +Array.from(target.options).filter((item) => item.selected)[0].value);
    },
  },
};
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
        <a class="page-link lh-sm py-1" href="#" aria-label="Previous" @click.stop="() => changePage(1)">
          <span aria-hidden="true">&laquo;</span>
        </a>
      </li>
      <li v-for="page in pages" :key="page" class="page-item">
        <a class="page-link lh-sm py-1" :class="{ active: page === current }" href="#" @click.stop="() => changePage(page)">{{ page }}</a>
      </li>
      <li class="page-item">
        <a class="page-link lh-sm py-1" href="#" aria-label="Next" @click.stop="() => changePage(lastPage)">
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
