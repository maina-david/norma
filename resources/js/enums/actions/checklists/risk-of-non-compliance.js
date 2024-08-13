function getLabel(risk) {
  return {
    0: 'Low',
    1: 'Medium',
    2: 'High',
    3: 'No Answer',
  }[risk] ?? '-';
}

function getColor(risk) {
  return {
    0: 'bg-primary text-white',
    1: 'bg-warning text-white',
    2: 'bg-red-500 text-white',
  } [risk] ?? '';
}

const RiskOfNonCompliance = {
  low: 0,
  medium: 1,
  high: 2,
  noAnswer: 3,

  forSelector() {
    return [
      { value: this.low, label: getLabel(this.low) },
      { value: this.medium, label: getLabel(this.medium) },
      { value: this.high, label: getLabel(this.high) },
      { value: this.noAnswer, label: getLabel(this.noAnswer) },
    ];
  },
};

export { RiskOfNonCompliance, getLabel , getColor };
