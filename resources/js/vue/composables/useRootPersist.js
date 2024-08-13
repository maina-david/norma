import { ref } from 'vue';

const store = {};
const promises = {};

export default function useRootPersist({ defaultValue, key, fetchData }) {
  // const getPersist = inject('getPersist');
  // const setPersist = inject('setPersist');
  const stored = ref(defaultValue);
  const loading = ref(true);

  function getPersist(key) {
    return store[key];
  }

  function setPersist(key, val) {
    store[key] = val;
  }

  function evaluate() {
    const saved = getPersist(key);

    if (saved) {
      stored.value = saved;
      loading.value = false;
      return;
    }

    if (promises[key]) {
      promises[key].then(( response ) => {
        stored.value = response;
        loading.value = false;
      });

      return;
    }

    promises[key] = fetchData()
      .then((response) => {
        setPersist(key, response);
        stored.value = response;

        return response;
      })
      .finally(() => {
        loading.value = false;
        promises[key].value = null;
      });
  }

  evaluate();

  return { stored, loading };
}
