export default (editor) => {
  editor.ui.registry.addContextToolbar('textselection', {
    predicate: () => !editor.selection.isCollapsed(),
    items: 'customNumber customHeading | customTerm | customAddNote | removeformat',
    position: 'selection',
    scope: 'node',
  });
};
