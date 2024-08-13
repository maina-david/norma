function getIcon(item) {
  return {
    assessment_item_response: 'clipboard-list-check',
    context_question: 'ballot-check',
    file: 'file-alt',
    place: 'tasks',
    register_item: 'gavel',
    register_notification: 'bell',
  }[item] ?? 'tasks';
}
function getLabel(item) {
  return {
    assessment_item_response: 'Assess Task',
    context_question: 'Applicability Task',
    file: 'File Task',
    place: 'Generic Task',
    register_item: 'Requirements Task',
    register_notification: 'Update Task',
  }[item] ?? 'Generic Task';
}

const TaskTypes = {
  // assess: 'assessment_item_response',
  context: 'context_question',
  file: 'file',
  generic: 'place',
  requirements: 'register_item',
  updates: 'register_notification',

  forSelector() {
    return [
      // { value: 'assess', label: getLabel(this.assess) },
      { value: 'context', label: getLabel(this.context) },
      { value: 'file', label: getLabel(this.file) },
      { value: 'generic', label: getLabel(this.generic) },
      { value: 'requirements', label: getLabel(this.requirements) },
      { value: 'updates', label: getLabel(this.updates) },
    ];
  },

  forGrouping() {
    return [
      // { value: [`taskable_type,eq,${this.assess}`], label: getLabel(this.assess) },
      { value: [`taskable_type,eq,${this.context}`], label: getLabel(this.context) },
      { value: [`taskable_type,eq,${this.file}`], label: getLabel(this.file) },
      { value: [`taskable_type,eq,${this.generic}`], label: getLabel(this.generic) },
      { value: [`taskable_type,eq,${this.requirements}`], label: getLabel(this.requirements) },
      { value: [`taskable_type,eq,${this.updates}`], label: getLabel(this.updates) },
    ];
  },
};

export { TaskTypes, getIcon };
