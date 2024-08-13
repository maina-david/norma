import { reactive } from 'vue';

export const useBulkActions = () => {
  const selected = reactive({});
  const selectedObjects = reactive({});

  const toggleSelected = (key, keyObject = null) => {
    selected[key] = !selected[key];
    if (keyObject) {
      selectedObjects[key] = { ...keyObject };
    }
  };
  const setSelected = (keys, isSelected) => {
    keys.forEach((key) => {
      selected[`${key}`] = isSelected;
    });
  };
  const setSelectedObject = (items) => {
    items.forEach((item) => {
      selectedObjects[item.id] = item;
    });
  };

  return { selected, selectedObjects, toggleSelected, setSelected, setSelectedObject };
};
