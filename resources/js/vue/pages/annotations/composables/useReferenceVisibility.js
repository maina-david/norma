import { computed, reactive } from 'vue';

export const ReferenceVisibility = {
  ALL: 0,
  PENDING: 1,
  APPLIED: 2,
  PENDING_OR_APPLIED: 3,
};

const filterNames = {
  assessmentItems: 'has-assessments',
  categories: 'has-topics',
  contextQuestions: 'has-context',
  legalDomains: 'has-domains',
  locations: 'has-jurisdictions',
  tags: 'has-tags',
  summary: 'has-summary',
  requirement: 'has-requirement',
  linking: 'has-links',
  comments: 'has-comments',
  assessmentItemsWithDrafts: 'assessmentItemsWithDrafts',
  questionsWithDrafts: 'questionsWithDrafts',
  locationsWithDrafts: 'locationsWithDrafts',
  domainsWithDrafts: 'domainsWithDrafts',
  topicsWithDrafts: 'topicsWithDrafts',
};

export const useReferenceVisibility = (init = {}) => {
  const referenceVisibility = reactive({
    assessmentItems: ReferenceVisibility.ALL,
    categories: ReferenceVisibility.ALL,
    contextQuestions: ReferenceVisibility.ALL,
    legalDomains: ReferenceVisibility.ALL,
    locations: ReferenceVisibility.ALL,
    tags: ReferenceVisibility.ALL,
    summary: ReferenceVisibility.ALL,
    requirement: ReferenceVisibility.ALL,
    linking: ReferenceVisibility.ALL,
    comments: ReferenceVisibility.ALL,
    assessmentItemsWithDrafts: [],
    questionsWithDrafts: [],
    locationsWithDrafts: [],
    domainsWithDrafts: [],
    topicsWithDrafts: [],
    ...init,
  });

  const fillableFilters = [
    'assessmentItemsWithDrafts', 'questionsWithDrafts', 'locationsWithDrafts', 'domainsWithDrafts', 'topicsWithDrafts',
  ];

  const params = Object.fromEntries((new URLSearchParams(window.location.search)).entries());
  Object.keys(params).forEach((key) => {
    const fillableKey = `${key.split('[')[0]}WithDrafts`;

    if (fillableFilters.includes(fillableKey)) {
      referenceVisibility[fillableKey].push(Number(params[key]));
    }
  });

  function toggleReferenceVisibility(item) {
    if (referenceVisibility[item] === ReferenceVisibility.PENDING_OR_APPLIED) {
      referenceVisibility[item] = ReferenceVisibility.ALL;
      return;
    }

    referenceVisibility[item] = referenceVisibility[item] + 1;
  }
  function setDetailedFilters(attributes) {
    Object.keys(attributes).forEach((key) => {
      referenceVisibility[key] = [...attributes[key]];
    });
  }

  const visibilityFilter = computed(() => {
    const params = {};

    Object.keys(referenceVisibility).forEach((key) => {
      if (Array.isArray(referenceVisibility[key]) && referenceVisibility[key].length > 0) {
        params[filterNames[key]] = referenceVisibility[key];
        return;
      }

      if (referenceVisibility[key] !== ReferenceVisibility.ALL) {
        params[filterNames[key]] = referenceVisibility[key];
      }
    });

    return params;
  });

  return { referenceVisibility, setDetailedFilters, visibilityFilter, toggleReferenceVisibility };
};
