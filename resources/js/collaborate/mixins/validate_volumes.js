export default {
  data() {
    return {
      errors: [],
      errorsVisible: false,
      currentValidated: 0,
      validating: false,
    };
  },
  methods: {
    validateVolumes(volume = 1) {
      if (this.externalContent) {
        return Promise.reject();
      }

      this.validating = true;

      return this.fetchRelativeContent({ expressionId: this.expressionId, volume })
        .then((data) => {
          let content = data.replace(/&nbsp;/gim, ' ')
            .replace(/text-indent:\s*-(\d+\w+;)?/g, 'text-indent: $1')
            .replace(/href=(["'])[^"']*\/files\//g, 'href=$1https://my.libryo.com/files/');

          content = (new DOMParser()).parseFromString(content, 'text/html');
          content.querySelectorAll('[data-inline="num"][data-id],[data-inline="heading"][data-id]').forEach((item) => {
            if (item.textContent.length > 800) {
              const dataId = item.getAttribute('data-id');
              const dataInline = item.getAttribute('data-inline');
              this.errors.push({
                selector: `[data-inline="${dataInline}"][data-id="${dataId}"]`,
                volume,
                error: 'Selected text is too long.',
                text: item.textContent,
              });
            }
          });

          content = null;

          this.currentValidated = volume;

          if (volume < this.lastVolume) {
            return this.validateVolumes(volume + 1);
          }

          return data;
        })
        .then(() => {
          if (this.errors.length > 0) {
            window.toast.error({ message: 'This document contains errors! Please fix the errors, save, then try again.', timeout: 10000 });
            this.errorsVisible = true;
            return Promise.reject();
          }
          return true;
        })
        .finally(() => {
          this.loading = false;
          this.validating = false;
        });
    },
  },
};
