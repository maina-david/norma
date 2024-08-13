export default class TableFilter {
  name;
  label;
  field;
  type = 'select';
  multiple = false;
  options = [];
  value = null;
  component = null;
  shouldShow = null;

  constructor({ name, label, type = 'select', component = null, multiple = false, options = [], value = null, shouldShow = null }) {
    this.name = name;
    this.label = label;
    this.type = type;
    this.multiple = multiple;
    this.options = options;
    this.value = value;
    this.component = component;
    this.shouldShow = shouldShow;
  }
}
