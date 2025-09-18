import 'tinymce';
import 'tinymce/icons/default/icons';
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/code';
import 'tinymce/plugins/emoticons';
import 'tinymce/plugins/emoticons/js/emojis';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/help';
import 'tinymce/plugins/help/js/i18n/keynav/en';
import 'tinymce/plugins/image';
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/media';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/table';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/wordcount';
import 'tinymce/themes/silver';
import 'tinymce/models/dom';

const fullPlugins = 'advlist autolink lists link image charmap print preview searchreplace visualblocks fullscreen media table help wordcount';

const fullToolbar = [
  'undo redo | bold italic backcolor | bullist numlist outdent indent | formatselect | alignleft aligncenter alignright alignjustify | removeformat emoticons| help',
];

const basicPlugins = 'autolink lists link image searchreplace fullscreen media table help wordcount emoticons';

const basicToolbar = [
  'undo redo | bold italic | bullist numlist outdent indent | formatselect | alignleft aligncenter alignright alignjustify | removeformat emoticons | help',
];

const minimalToolbar = [
  'undo redo | bold italic | bullist numlist outdent indent | alignleft aligncenter alignright alignjustify | removeformat emoticons',
];
const minimalPlugins = 'autolink lists link searchreplace emoticons';

const commonSettings = {
  emoticons_database: 'emojis',
  paste_as_text: true,
  skin: false,
  content_css: false,
  body_class: 'notranslate',
  min_height: 300,
  branding: false,
  ui: {
    iframe: {
      classes: 'notranslate',
    },
  },
  setup: (editor) => {
    editor.on('change', (e) => {
      editor.targetElm.value = editor.getContent();
      editor.targetElm.dispatchEvent(new CustomEvent('input'));
    });
  },
};

window.initTiny = ({ maxHeight } = {}) => {
  const extraSettings = {};

  if (maxHeight) {
    extraSettings.min_height = maxHeight;
    extraSettings.max_height = maxHeight;
  }

  tinymce.remove();

  tinymce.init({
    selector: '.norma-editor-full',
    plugins: fullPlugins,
    toolbar: fullToolbar,
    ...commonSettings,
    ...extraSettings,
  });

  tinymce.init({
    selector: '.norma-editor-content',
    plugins: fullPlugins,
    toolbar: fullToolbar,
    paste_as_text: false,
    skin: false,
    content_css: false,
    min_height: 400,
    branding: false,
    body_class: 'notranslate',
    ...extraSettings,
  });

  tinymce.init({
    selector: '.norma-editor-basic',
    plugins: basicPlugins,
    toolbar: basicToolbar,
    ...commonSettings,
    ...extraSettings,
  });

  tinymce.init({
    selector: '.norma-editor-minimal',
    plugins: minimalPlugins,
    toolbar: minimalToolbar,
    menubar: false,
    statusbar: false,
    ...commonSettings,
    ...extraSettings,
  });
};
