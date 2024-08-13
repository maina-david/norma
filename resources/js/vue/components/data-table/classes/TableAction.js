export default class TableAction {
  name;
  label;
  component = null;
  type = 'select';
  options = [];
  value = null;

  constructor({ name, label, component = null, type = 'select', options = [], value = null }) {
    this.name = name;
    this.label = label;
    this.component = component;
    this.options = options;
    this.type = type;
    this.value = value;
  }
}
