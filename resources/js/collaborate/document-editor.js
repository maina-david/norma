import { Transformers } from 'legal-doc-html';
import { debounce } from 'lodash';
import editorConfig from "./legal-doc-html/editorConfig";
import pastePreProcess from './legal-doc-html/pastePreProcess';
import pastePostProcess from './legal-doc-html/pastePostProcess';

const config = editorConfig({
  getNextVolume: () => {
    const editor = document.querySelector('#norma-document-editor');
    const current = parseInt(editor.getAttribute('data-current'), 10);
    const last = parseInt(editor.getAttribute('data-last'), 10);

    if (current !== last) {
      window.toast.error({ title: 'Volumes', message: 'You can only modify volumes when editing the last available volume.' });
      return null;
    }

    return current === 1 ? 1 : last - 1;
  },
});

const commonSettings = {
  ...config,
  skin: false,
  content_css: false,
  branding: false,
  height: '100%',
  paste_preprocess: (plugin, args) => {
    try {
      const source = document.querySelector('#legal_doc_source').selectedOptions[0];

      return pastePreProcess(plugin, args, source)
    } catch (e) {
      return args.content;
    }
  },
  paste_postprocess: (plugin, args) => {
    try {
      const source = document.querySelector('#legal_doc_source').selectedOptions[0];

      return pastePostProcess(plugin, args, source)
    } catch (e) {
      return args.content;
    }
  },
  formats: {
    ...config.formats,
    custom_apply_level: {
      block: 'p',
      attributes: { 'data-type': '%value' },
    },
  },
};

const init = debounce(() => {
  tinymce.remove();

  tinymce.init({
    selector: '#norma-document-editor',
    ...commonSettings,
  });
}, 1500);


init();

document.addEventListener('turbo:load', () => init());

document.addEventListener('turbo:frame-load', () => init());

document.addEventListener('turbo:before-stream-render', () => init());


