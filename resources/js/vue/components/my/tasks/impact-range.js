const labeled = {
  1: '1 - Low',
  5: '5 - Medium',
  10: '10 - High',
};

export default Array(10)
  .fill('')
  .map((item, index) => ({ label: labeled[index +1] ?? index +1, value: index + 1 }));
