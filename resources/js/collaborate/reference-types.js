export const namesAsKey = {
  TYPE_CONSEQUENCE_GROUP: 6,
  TYPE_OBLIGATION: 2,
  TYPE_REQUIREMENT: 2,
  TYPE_PROHIBITION: 3,
  TYPE_BEARER: 1,
  TYPE_EXCEPTION: 4,
  TYPE_PROCEDURAL: 8,
  TYPE_TECHNICAL: 7,
  TYPE_RIGHT: 5,
  TYPE_APPLICABILITY: 10,
  TYPE_CONSTITUTIVE: 9,
  TYPE_CITATION: 11,
  TYPE_AUTHORITY_POWER: 12,
  TYPE_AUTHORITY_OBLIGATION: 13,
  TYPE_EXPLANATORY: 14,
  TYPE_DEEMING: 15,
  TYPE_WORK: 16,
  TYPE_AMENDMENT: 17,
  TYPE_PROCESS: 18,
};

export const typeAsKey = {
  // 1: 'Bearer',
  2: 'Requirement',
  4: 'Exception/Permission',
  // 5: 'Right',
  6: 'Consequence group',
  7: 'Technical',
  8: 'Procedural',
  // 9: 'Constitutive',
  // 10: 'Applicability',
  // 11: 'Citation',
  // 12: 'Authority\'s power',
  // 13: 'Authority\'s obligation',
  // 14: 'Explanatory',
  // 15: 'Deeming',
  // 16: 'Work',
  17: 'Amendment',
  // 18: 'Process',
};
export const typeAsKeyOfHasFields = {
  2: 'has_obligations',
  4: 'has_exceptions',
  // 5: 'has_rights',
  6: 'has_consequences',
  7: 'has_technical',
  8: 'has_procedural',
  17: 'has_amendments',
};

export const shortNameAsValue = {
  1: 'Bea',
  2: 'Obl',
  3: 'Pro',
  4: 'Pms',
  5: 'Rig',
  6: 'Con',
  7: 'Tec',
  8: 'Pro',
  9: 'Cnt',
  10: 'App',
  11: 'Cit',
  12: 'APo',
  13: 'AOb',
  14: 'Exp',
  15: 'Dee',
  16: 'Wor',
  17: 'Amm',
  18: 'Pss',
};

export const linkTypes = {
  LINK_TYPE_READ_WITH: 1,
  LINK_TYPE_OPTIONAL_READ_WITH: 2,
  LINK_TYPE_CONSEQUENCE: 3,
  LINK_TYPE_CONSTITUTIVE: 4,
  LINK_TYPE_BEARER: 5,
  LINK_TYPE_AMENDMENT: 6,
};

export const linkTypeDescriptions = {
  legal_statement_parent_link_type_1: 'Mandatory Read-With',
  legal_statement_parent_link_type_2: 'Optional Read-With',
  legal_statement_parent_link_type_3: 'Requirements leading to these consequences',
  legal_statement_parent_link_type_4: 'Constituted By',
  legal_statement_parent_link_type_5: 'Is Borne By',
  legal_statement_parent_link_type_6: 'Amended By',
  legal_statement_child_link_type_1: 'Mandatory Read-With',
  legal_statement_child_link_type_2: 'Optional Read-With',
  legal_statement_child_link_type_3: 'Leads to Consequences',
  legal_statement_child_link_type_4: 'Constitutes',
  legal_statement_child_link_type_5: 'Is the Bearer of',
  legal_statement_child_link_type_6: 'Amends',
};

export const hasFieldsOptions = [
  'has_obligations',
  'has_rights',
  'has_amendments',
  'has_exceptions',
  'has_procedural',
  'has_technical',
  'has_consequences',
];

export const hasFieldNamesOptions = {
  has_obligations: 'Requirements',
  has_rights: 'Right',
  has_amendments: 'Amendment',
  has_exceptions: 'Exception',
  has_procedural: 'Procedural',
  has_technical: 'Technical',
  has_consequences: 'Consequence',
};

export const hasFieldTypeNumbers = {
  has_obligations: 2,
  has_rights: 5,
  has_amendments: 17,
  has_exceptions: 4,
  has_procedural: 8,
  has_technical: 7,
  has_consequences: 6,
};
