export default class TableColumn {
  name;
  label;
  sortable = true;
  align = 'center';
  field = null;
  date = false;
  href = null;
  component = null;
  minWidth = null;

  constructor({ name, label, sortable = true, align = 'center', field = null, date = false, href = null, component = null, minWidth = null }) {
    this.name = name;
    this.label = label;
    this.sortable = sortable;
    this.align = align;
    this.field = field;
    this.date = date;
    this.href = href;
    this.component = component;
    this.minWidth = minWidth;
  }

  rowField() {
    return this.field ?? this.name;
  }
}
