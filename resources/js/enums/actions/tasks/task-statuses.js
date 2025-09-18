function getColour(status) {
  return {
    1: 'text-warning',
    2: 'text-primary',
  }[status] ?? 'text-norma-gray-500';
}

function getBackgroundColour(status) {
  return {
    0: 'bg-negative text-white',
    1: 'bg-info text-norma-gray-800',
    2: 'bg-primary text-white',
    3: 'bg-warning',
  }[status] ?? '';
}

function getComputedBackgroundColour(status) {
  return {
    0: window.getComputedStyle(document.body).getPropertyValue('--negative'),
    1: window.getComputedStyle(document.body).getPropertyValue('--info'),
    2: window.getComputedStyle(document.body).getPropertyValue('--primary'),
    3: window.getComputedStyle(document.body).getPropertyValue('--warning'),
  }[status] ?? '';
}

function getLabel(status) {
  return {
    0: 'Not Started',
    1: 'In Progress',
    2: 'Done',
    3: 'Paused',
  }[status] ?? '-';
}

const TaskStatus = {
  notStarted: '0',
  inProgress: '1',
  done: '2',
  paused: '3',

  forSelector() {
    return [
      { value: this.notStarted, label: getLabel(this.notStarted) },
      { value: this.inProgress, label: getLabel(this.inProgress) },
      { value: this.done, label: getLabel(this.done) },
      { value: this.paused, label: getLabel(this.paused) },
    ];
  },

  forGrouping() {
    return [
      { value: [`task_status,eq,${this.notStarted}`], label: getLabel(this.notStarted) },
      { value: [`task_status,eq,${this.inProgress}`], label: getLabel(this.inProgress) },
      { value: [`task_status,eq,${this.done}`], label: getLabel(this.done) },
      { value: [`task_status,eq,${this.paused}`], label: getLabel(this.paused) },
    ];
  },
};

export { TaskStatus, getBackgroundColour, getComputedBackgroundColour, getColour, getLabel };
