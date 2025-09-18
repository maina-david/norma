<script>
export default {
  name: 'TocReferenceTypeSelector',
  props: {
    label: { type: String, default: 'T' },
  },
  data() {
    return {
      visible: false,
      types: [
        'Annex',
        'Article',
        'Chapter',
        'Division',
        'Part',
        'Schedule',
        'Section',
        'Title',
      ],
    };
  },
  methods: {
    changeType(item) {
      this.$emit('change', item.toLowerCase());
      this.visible = false;
    }
  }
};
</script>

<template>
  <div class="relative" role="group">
    <button @click.stop.prevent="visible = !visible" type="button" class="btn btn-outline-secondary" style="border-top-left-radius:0;border-bottom-left-radius:0;border-top-right-radius:0.25rem;border-bottom-right-radius:0.25rem;border-right:1px solid">
      <span class="mr-1">{{ label }}</span>
      <norma-icon name="angle-down" />
    </button>
    <ul class="absolute bg-white w-48 left-0 top-8 z-10 rounded-lg shadow-lg" v-show="visible">
      <li v-for="item in types" :key="item" class="px-4 py-1 border-b border-gray-100">
        <a class="dropdown-item" href="#" @click.prevent="() => changeType(item)">{{ item }}</a>
      </li>
    </ul>
  </div>
</template>

<style scoped>

</style>
