import {
  formatNum,
  formatHeading,
  formatTerm,
  formatType,
  formatLevel,
  copyBlockIdToClipboard,
  volumeBreak,
} from './formatActions';

import { levelElements } from './menuElements';

export default (editor, props) => {
  editor.ui.registry.addButton('customAddVolume', {
    icon: 'page-break',
    tooltip: 'Insert volume break at the cursor position.',
    onAction: () => volumeBreak(editor, props.getNextVolume),
  });
  editor.ui.registry.addButton('customNumber', {
    text: 'Number',
    tooltip: 'Set as Number (ctrl + n)',
    onAction: () => formatNum(editor),
  });

  editor.ui.registry.addButton('customHeading', {
    text: 'Heading',
    tooltip: 'Set as Heading (ctrl + h)',
    onAction: () => formatHeading(editor),
  });

  editor.ui.registry.addButton('customTerm', {
    text: 'Term',
    tooltip: 'Set as Term (ctrl + t)',
    onAction: () => formatTerm(editor),
  });

  editor.ui.registry.addButton('customAddNote', {
    icon: 'document-properties',
    tooltip: 'Set block as note',
    onAction: () => formatType(editor, 'note'),
  });

  editor.ui.registry.addButton('customCopyId', {
    icon: 'duplicate',
    tooltip: 'Copy block ID to clipboard',
    onAction: () => {
      copyBlockIdToClipboard(editor);
    },
  });

  editor.ui.registry.addMenuButton('type', {
    text: 'Blocks',
    fetch: (callback) => {
      const items = [];
      levelElements.forEach((el) => {
        items.push({
          type: 'menuitem',
          text: el.text,
          onAction: () => formatType(editor, el.value),
        });
      });
      callback(items);
    },
  });

  editor.ui.registry.addMenuButton('levels', {
    text: 'Heading Level',
    fetch: (callback) => {
      const items = [];

      items.push({
        type: 'menuitem',
        text: 'No Level',
        onAction: () => formatLevel(editor, null),
      });

      Array(20).fill().forEach((item, index) => {
        items.push({
          type: 'menuitem',
          text: `Level ${index + 1}`,
          onAction: () => formatLevel(editor, index + 1),
        });
      });

      callback(items);
    },
  });
};
