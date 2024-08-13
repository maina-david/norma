import css from './css';
import setup from './setup';
import { indent, indentUnit } from './defaults';

export default (props) => ({
  // inline: true,
  indent_use_margin: true,
  indentation: `${indent}${indentUnit}`,
  plugins: 'paste table image noneditable code visualblocks link',
  table_default_styles: {
    'border-collapse': 'collapse',
    width: '100%',
  },
  table_sizing_mode: 'responsive',
  // toolbar: 'customHeading customNumber customTerm | type levels customAddNote customAddVolume | indent outdent | bold italic underline | superscript subscript | table image | alignleft aligncenter alignright alignnone | link unlink | customCopyId | code',
  toolbar: '| customAddNote customAddVolume | indent outdent | bold italic underline | superscript subscript | table image | alignleft aligncenter alignright alignnone | link unlink | customCopyId | code',
  toolbar_sticky: true,
  menubar: false,
  content_style: css,
  setup: (ed) => setup(ed, props),
  entity_encoding: 'raw',
  invalid_elements: 'ul,ol,div',
  valid_classes: {
    '*': 'indent num heading', // Global classes
  },
  valid_styles: {
    '*': 'margin,margin-left,margin-right,text-align',
  },
  formats: {
    custom_apply_inline: {
      inline: 'span',
      attributes: { 'data-inline': '%value' },
      exact: true,
      block_expand: false,
      expand: false,
    },
    custom_apply_type: {
      block: 'p',
      attributes: { 'data-type': '%value' },
      // exact: true,
      // wrapper: true,
      // block_expand: true,
      // deep: true,
    },
    custom_set_level: {
      block: 'p',
      attributes: { 'data-level': '%value' },
      // exact: true,
      // wrapper: true,
      // block_expand: true,
      // deep: true,
    },
    removeformat: [
      {
        selector: '*', attributes: ['data-level', 'data-indent'], remove: 'empty', split: true, expand: false, deep: false,
      },
    ],
  },
  visualblocks_default_state: true,
  keep_styles: false,
  convert_urls: false,
});
