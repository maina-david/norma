import getSelection from './selection/getSelection';

export const registerSelectionListener = (element, emptyCllBck, cllBck) => {
  const returnListener = (e) => {
    if (getSelection().isCollapsed) {
      emptyCllBck();
      return;
    }

    cllBck(e);
  };
  element.addEventListener('mouseup', returnListener);

  return returnListener;
};
export const deregisterSelectionListener = (element, listener) => {
  element.removeEventListener('mouseup', listener);
};
