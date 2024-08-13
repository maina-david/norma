import cuid from 'cuid';

const marginMultiplier = 30;

const prepareHtmlForEditor = (content) => {
  const parser = new DOMParser().parseFromString(content, 'text/html');

  Array.from(parser.querySelectorAll('[data-indent]'))
    .forEach((el) => {
      const indent = parseInt(el.getAttribute('data-indent'), 10) * marginMultiplier;
      let style = el.getAttribute('style') || '';
      if (style.length > 0 && style.substr(style.length - 1) !== ';') {
        style += ';';
      }

      el.setAttribute('style', `${style}margin-left:${indent}px`);
    });

  return `${parser.body.innerHTML}\n`;
};

const prepareHtmlForSaving = (content) => {
  const parser = new DOMParser().parseFromString(content, 'text/html');

  Array.from(parser.querySelectorAll('[data-level]'))
    .forEach((el) => {
      const pairs = Array.from(el.querySelectorAll('[data-inline="num"], [data-inline="heading"]'));
      if (pairs.length > 0) {
        const hasId = pairs.every((inline) => inline.hasAttribute('data-id'));
        if (!hasId) {
          const id = cuid();
          pairs.forEach((pair) => pair.setAttribute('data-id', id));
        }
      }
    });

  Array.from(parser.querySelectorAll('[style],[data-indent]'))
    .forEach((el) => {
      let style = el.getAttribute('style');
      const indent = el.getAttribute('data-indent');

      if (!style && indent) {
        el.removeAttribute('data-indent');
        return;
      }

      const matches = style.match(/margin-left:\s*([0-9]+)px/);

      if (!matches) {
        return;
      }

      el.setAttribute('data-indent', parseInt(matches[1], 10) / marginMultiplier);

      style = style.replace(/margin-left:\s*[0-9]+px;?/, '').trim();
      if (style.length < 1) {
        el.removeAttribute('style');
        return;
      }

      el.setAttribute('style', style.replace(/margin-left:\s*[0-9]+px;?/, ''));
    });

  return `${parser.body.innerHTML}\n`;
};

export { prepareHtmlForEditor, prepareHtmlForSaving };
