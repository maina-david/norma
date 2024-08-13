import * as FilePond from 'filepond';
import 'filepond/dist/filepond.min.css';

window.initFilepond = () => document.querySelectorAll('.filepond')
  .forEach((item) => {
    const target = item.getAttribute('data-url');
    const parentForm = item.closest('form');

    FilePond.create(item, {
      maxParallelUploads: 20,
      allowReplace: false,
      allowRevert: false,
      allowRemove: false,
      server: {
        process: (fieldName, file, metadata, load, error, progress, abort) => {
          // set data
          const formData = new FormData();
          formData.append('filepond', 1);
          formData.append(fieldName, file, file.name);

          Array.from(parentForm.elements)
            .slice(0, Array.from(parentForm.elements).findIndex((i) => (i.getAttribute('id') || '').match('filepond')))
            .forEach((el) => {
              if (!el.getAttribute('name')) {
                return;
              }

              if (el.nodeName !== 'SELECT') {
                formData.append(el.getAttribute('name'), el.value);
                return;
              }

              Array.from(el.selectedOptions).forEach((option) => {
                formData.append(el.getAttribute('name'), option.value);
              });
            });

          // related to aborting the request
          const CancelToken = axios.CancelToken;
          const source = CancelToken.source();

          // the request itself
          window.axios({
            method: 'POST',
            url: target,
            data: formData,
            cancelToken: source.token,
            onUploadProgress: (e) => {
              progress(e.lengthComputable, e.loaded, e.total);
            },
          }).then((response) => {
            load(Math.random()* 100000);
          }).catch((thrown) => {
            if (axios.isCancel(thrown)) {
              console.log('Request canceled', thrown.message);
            } else if (thrown.response.status === 422) {
              error(thrown.response.data.message);
              window.toast.error({ message: thrown.response.data.message });
              // handle error
            }
          });

          // Setup abort interface
          return {
            abort: () => {
              source.cancel('Operation canceled by the user.');
              abort();
            },
          };
        },
      },
    });
  });
