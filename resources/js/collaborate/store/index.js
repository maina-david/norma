import {createStore} from 'vuex'

export default createStore({

  modules: {
    annotations: {
      namespaced: true,
      state: () => ({
        showHighlighterDialog: false,
        currentRange: null,
        currentTextSelection: '',
        currentParagraph: null,
      }),
      mutations: {
        setState(state, payload) {
          Object.keys(payload).forEach((key) => {
            state[key] = payload[key];
          });
        },
      },
    },
  },
});
