import mitt from 'mitt';

const emitter = mitt();

export const confirm = (payload) => new Promise((resolve, reject) => {
  emitter.emit('confirm', { ...payload, resolve, reject });
});

export default {
  install(app) {
    /* eslint-disable  no-param-reassign */
    app.config.globalProperties.$mitt = () => emitter;
    app.config.globalProperties.$onEvent = (event, handler) => emitter.on(event, handler);
    app.config.globalProperties.$confirm = confirm;
  },
};
