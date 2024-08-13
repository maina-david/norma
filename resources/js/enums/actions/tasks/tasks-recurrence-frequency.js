function getLabel(item) {
  return {
    1: 'Day',
    2: 'Week',
    3: 'Month',
    4: 'Year',
  }[item] ?? '-';
}

const Frequency = {
  day: 1,
  week: 2,
  month: 3,
  year: 4,

  forSelector() {
    return [
      { value: this.day, label: getLabel(this.day) },
      { value: this.week, label: getLabel(this.week) },
      { value: this.month, label: getLabel(this.month) },
      { value: this.year, label: getLabel(this.year) },
    ];
  },
};

const MonthSelection = {
  DAY_OF_MONTH: 'dayOfMonth',
  WEEK_OF_MONTH: 'weekOfMonth',
};

export { Frequency, MonthSelection, getLabel };
