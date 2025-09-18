export default () => {
  return document.all ? document.selection.createRange().text : document.getSelection();
};
