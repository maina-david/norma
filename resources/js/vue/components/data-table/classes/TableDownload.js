export default class TableDownload {
  type;
  icon;

  constructor({ type, icon = 'download' }) {
    this.icon = icon;
    this.type = type;
  }
}
