function getLabel(item) {
  return {
    0: 'Very Low',
    1: 'Low',
    2: 'Medium',
    3: 'High',
    4: 'Very High',
  }[item] ?? '-';
}

const TaskPriority = {
  veryLow: '0',
  low: '1',
  medium: '2',
  high: '3',
  veryHigh: '4',

  forSelector() {
    return [
      { value: this.veryLow, label: getLabel(this.veryLow) },
      { value: this.low, label: getLabel(this.low) },
      { value: this.medium, label: getLabel(this.medium) },
      { value: this.high, label: getLabel(this.high) },
      { value: this.veryHigh, label: getLabel(this.veryHigh) },
    ];
  },
};

export { TaskPriority, getLabel };
