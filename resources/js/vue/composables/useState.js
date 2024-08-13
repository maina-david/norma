import { ref } from 'vue';

export const useState = (defaultValue) => {
  const state = ref(defaultValue);

  function setState(value) {
    state.value = value;
  }

  return [state, setState];
};
