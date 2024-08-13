import { ref } from 'vue';
import { useUrlSearchParams } from '@vueuse/core';

export function useModelFilters({ available, preset = {}, fixedFilters = {} }) {
  const params = useUrlSearchParams('history');
  const search = ref(params.search ?? '');
  const appliedFilters = ref([]);

  const paramKeys = Object.keys(params);

  function updateParamFromItem(item) {
    if (item.multiple) {
      item.value.forEach((sel, index) => {
        params[`${item.name}[${index}]`] = sel;
      });
    } else {
      params[item.name] = item.value;
    }
  }

  function getValueFromParam(item) {
    if (!item.multiple) {
      return params[item.name] ?? null;
    }

    const matched = [];

    paramKeys.forEach((key) => {
      if (key.startsWith(`${item.name}[`)) {
        matched.push(params[key]);
      }
    });

    return matched;
  }

  const availableFilters = ref([
    ...available
      .filter((item) => !item.shouldShow || item.shouldShow())
      .map((item) => {
        const current = { ...item };

        if (preset[current.name]) {
          current.value = preset[current.name];
          // appliedFilters.value.push({ ...current });

          return current;
        }

        const defaultValue = current.multiple ? [] : null;
        const fromParam = getValueFromParam(current);

        current.value = fromParam ?? defaultValue;

        if (fromParam !== null && fromParam.length > 0) {
        // appliedFilters.value.push({ ...current });
        }

        return current;
      }),
  ]);

  function applyFilters(callback = () => {}) {
    Object.keys(params).forEach((key) => {
      params[key] = null;
    });

    appliedFilters.value = availableFilters.value.map((fil) => {
      fil.value = fil.value !== '' ? fil.value : null;
      updateParamFromItem(fil);

      return { ...fil };
    });

    callback();
  }

  function getAppliedFilters() {
    const filters = {};

    appliedFilters.value.forEach((item) => {
      if (item.value !== null) {
        filters[item.name] = item.value;
      }
    });

    updateParamFromItem({ name: 'search', value: null });

    if (search.value.length > 2) {
      filters['search'] = search.value;
      updateParamFromItem({ name: 'search', value: search.value });
    }

    Object.keys(fixedFilters)
      .forEach((key) => {
        filters[key] = fixedFilters[key];
      });

    return { ...filters };
  }

  applyFilters(getAppliedFilters);

  return {
    getAppliedFilters,
    availableFilters,
    applyFilters,
    search,
  };
}
