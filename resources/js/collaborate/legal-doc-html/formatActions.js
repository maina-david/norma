import { indent } from './defaults';

const inlines = [
  'a', 'abbr', 'acronym', 'b', 'bdo', 'big', 'br', 'button', 'cite', 'code', 'dfn', 'em', 'i', 'img', 'input', 'kbd', 'label', 'map', 'object', 'output',
  'q', 'samp', 'script', 'select', 'small', 'span', 'strong', 'sub', 'sup', 'textarea', 'time', 'tt', 'var',
];

const findNonInline = (node) => {
  if (inlines.includes(node.nodeName.toLowerCase())) {
    return findNonInline(node.parentElement);
  }

  return node;
};

export const volumeBreak = (editor, getNextVolume) => {
  let volume = getNextVolume();

  if (!volume) {
    return;
  }

  let ref = editor.selection.getRng().endContainer;
  let position = 'afterend';
  if (editor.selection.getRng().startOffset < 2) {
    ref = editor.selection.getRng().startContainer;
    position = 'beforebegin';
  }

  if (ref.nodeType === Node.TEXT_NODE) {
    ref = ref.parentElement;
  }

  ref = findNonInline(ref);

  if (ref.getAttribute('data-end-of-volume')) {
    if (ref.textContent.length > 0 || ref.children.length > 0) {
      ref.removeAttribute('data-end-of-volume');
      return;
    }

    ref.remove();
    return;
  }

  const endOfVolumes = editor.dom.doc.querySelectorAll('[data-end-of-volume]');

  if (endOfVolumes.length > 0) {
    const lastVolume = endOfVolumes[endOfVolumes.length - 1].getAttribute('data-end-of-volume');
    volume = parseInt(lastVolume, 20) + 1;
  }

  const node = document.createElement('p');
  node.setAttribute('data-end-of-volume', volume);

  ref.insertAdjacentElement(position, node);
};

export const formatNum = (editor) => {
  editor.formatter.apply('custom_apply_inline', {
    value: 'num',
  });
};

export const formatHeading = (editor) => {
  editor.formatter.apply('custom_apply_inline', {
    value: 'heading',
  });
};

export const formatTerm = (editor) => {
  editor.formatter.apply('custom_apply_inline', {
    value: 'term',
  });
};

export const formatNoteRef = (editor) => {
  editor.formatter.apply('custom_apply_inline', {
    value: 'noteRef',
  });
};

export const formatType = (editor, level) => {
  if (level === null) {
    editor.formatter.apply('custom_apply_type', {
      value: 'none',
    });
  } else {
    editor.formatter.apply('custom_apply_type', {
      value: level,
    });
  }
};

export const formatLevel = (editor, level) => {
  if (level === null) {
    editor.formatter.apply('custom_set_level', { value: '' });
    editor.formatter.remove('custom_set_level');
  } else {
    editor.formatter.apply('custom_set_level', { value: level });
  }
};

export const setIndentByMargin = (element) => {
  if (element.style.marginLeft !== '') {
    element.setAttribute('data-indent', (parseInt(element.style.marginLeft, 10) / indent) + 1);
  } else {
    element.removeAttribute('data-indent');
  }
};

export const copyBlockIdToClipboard = (editor) => {
  const bl = editor.selection.getSelectedBlocks()[0];
  const el = document.createElement('textarea');
  const id = bl.getAttribute('id');
  if (!id) {
    return;
  }
  el.value = `#${id}`;
  document.body.appendChild(el);
  el.select();
  document.execCommand('copy');
  document.body.removeChild(el);
};
