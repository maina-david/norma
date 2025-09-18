<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import TomSelect from 'tom-select';
import { useAxios } from '@/vue/composables/useAxios';

const emit = defineEmits(['change', 'change-object']);
const axios = useAxios();
const props = defineProps({
  options: { type: Array, default: () => [] },
  value: { type: [Array, String, Number], default: () => [] },
  target: { type: String, required: true },
  placeholder: { type: String, default: 'Search...' },
  fetchOnLoad: { type: Boolean, default: false },
  withRemove: { type: Boolean, default: false },
  fetchOnTargetChange: { type: Boolean, default: false },
  searchField: { type: [Boolean, String], default: 'title' },
});

const element = ref(null);
const tom = ref(null);
const searchTarget = computed(() => props.target);

const selectedValue = computed(() => Array.isArray(props.value) ? props.value : [pros.value]);

onMounted(() => {
  tom.value = new TomSelect(element.value, {
    plugins: props.withRemove ? ['remove_button'] : [],
    valueField: 'id',
    labelField: 'title',
    searchField: props.searchField,
    placeholder: props.placeholder,
    maxOptions: null,
    create: false,
    onInitialize: function () {
      if (props.fetchOnLoad) {
        this.load(null);
      }
    },
    shouldLoad: (query) => {
      return props.fetchOnLoad || query.length >= 3;
    },
    load: function (query, callback) {
      axios.get(searchTarget.value, { params: { search: query } })
        .then((response) => response.data)
        .then((data) => {
          this.clearOptions();

          callback(data);

          // since we've loaded the items, no need to fetch again.
          if (props.fetchOnLoad) {
            this.settings.load = null;
          }
        }).catch(() => {
          callback();
        });
    },
    render: {
      not_loading: (data, escape) => {
        return '<div class="p-2 italic">\'Keep typing to search....\'</div>';
      },
      no_results: function (data, escape) {
        const text = `No results found for "'${escape(data.input)}'"`;

        return `<div class="p-2 italic">${text}</div>`;
      },
      option: function (data, escape) {
        const detailStr = data.details ? '<div class="text-norma-gray-500">' + escape(data.details) + '</div>' : '';
        return '<div>' +
            '<div>' + escape(data.title) + '</div>' +
            detailStr +
            '</div>';
      },
      item: function (data, escape) {
        return '<div title="' + escape(data.details || '') + '">' + escape(data.title) + '</div>';
      },
    },
  });

  function cleanOption(option) {
    return option ? { id: option.id, title: option.title } : option;
  }

  tom.value.on('change', (detail) => {
    const fullDetail = Array.isArray(detail)
      ? detail.map((item) => cleanOption(tom.value.options[item]))
      : cleanOption(tom.value.options[detail]);

    emit('change', detail);
    emit('change-object', fullDetail);
  });
  //
  // tom.value.setValue([...selectedValue.value]);
  //
  // if (selectedValue.value.length > 0 && !props.fetchOnLoad && props.target) {
  //   selectedValue.value.forEach((val) => {
  //     tom.value.search(val);
  //     tom.value.setValue([...selectedValue.value]);
  //   });
  // }
});

if (props.fetchOnTargetChange) {
  watch(searchTarget, () => {
    // check if the prop has changed.
    tom.value.load('');
  });
}

defineExpose({ tomselect: tom });

</script>

<template>
  <select ref="element" class="mr-2 text-primary focus:ring-primary border-gray-300 rounded-lg w-full">
    <option v-for="item in options" :key="item.id" :value="item.id" :selected="selectedValue.includes(item.id)">
      {{ item.title }}
    </option>
  </select>
</template>
