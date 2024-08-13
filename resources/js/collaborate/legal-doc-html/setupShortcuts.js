import {
  formatNum,
  formatHeading,
  formatTerm,
} from './formatActions';

export default (editor) => {
  editor.shortcuts.add('ctrl+n', 'Adds number', () => formatNum(editor));
  editor.shortcuts.add('ctrl+h', 'Adds heading', () => formatHeading(editor));
  editor.shortcuts.add('ctrl+t', 'Adds term', () => formatTerm(editor));

  editor.on('keydown', (event) => {
    if (event.keyCode === 9) {
      // tab pressed
      if (event.shiftKey) {
        editor.execCommand('outdent');
      } else {
        editor.execCommand('indent');
      }

      event.preventDefault();
      return false;
    }
    return true;
  });
};
