import setupShortcuts from './setupShortcuts';
import setupButtons from './setupButtons';
import setupContextToolbar from './setupContextToolbar';
import handleNodeChange from './handleNodeChange';
// import handleExecCommand from './handleExecCommand';

export default (editor, props) => {
  setupContextToolbar(editor);
  setupShortcuts(editor);
  setupButtons(editor, props);

  editor.on('NodeChange', handleNodeChange);
  // editor.on('ExecCommand', handleExecCommand);
};
