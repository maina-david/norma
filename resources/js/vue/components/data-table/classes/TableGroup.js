export default class TableGroup {
  field;
  options = [];
  setFilters = {};

  constructor({ field, options = [], setFilters = {} }) {
    this.field = field;
    this.options = options;
    this.setFilters = setFilters;
  }
}
