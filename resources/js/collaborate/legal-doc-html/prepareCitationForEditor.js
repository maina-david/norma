export default (citation) => {
  const p = new DOMParser().parseFromString(citation.content, 'text/html');

  const el = p.firstChild.lastChild.firstChild;
  el.setAttribute('id', citation.id);
  if (citation.type) {
    el.setAttribute('data-level', citation.type);
  }
  el.setAttribute('data-indent', citation.level);
  el.setAttribute('style', `${el.getAttribute('style') || ''} margin-left:${(citation.level - 1) * 30}px`);

  return `${el.outerHTML}\n`;
};
