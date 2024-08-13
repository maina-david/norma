import { computed, reactive } from 'vue';
import { useState } from '@/vue/composables/useState';

export const metaInfo = {
  actionAreas: {
    label: 'Action Areas',
    icon: 'clipboard-list-check',
    colour: 'text-cyan-600 bg-cyan-100',
    draft_field: 'action_area_drafts',
    field: 'action_areas',
    attach: 'collaborate.corpus.work-expression.attach-action-areas',
    detach: 'collaborate.corpus.work-expression.detach-action-areas',
    apply: 'collaborate.corpus.work-expression.apply-action-areas',
  },
  assessmentItems: {
    label: 'Assessment Items',
    icon: 'check',
    colour: 'text-purple-600 bg-purple-100',
    draft_field: 'assessment_item_drafts',
    field: 'assessment_items',
    attach: 'collaborate.corpus.work-expression.attach-assessment-items',
    detach: 'collaborate.corpus.work-expression.detach-assessment-items',
    apply: 'collaborate.corpus.work-expression.apply-assessment-items',
  },
  categories: {
    label: 'Topics',
    icon: 'hashtag',
    colour: (row) => `text-white bg-[${row.category_type.colour}]`,
    draft_field: 'category_drafts',
    field: 'categories',
    attach: 'collaborate.corpus.work-expression.attach-categories',
    detach: 'collaborate.corpus.work-expression.detach-categories',
    apply: 'collaborate.corpus.work-expression.apply-categories',
  },
  contextQuestions: {
    label: 'Context Questions',
    icon: 'question',
    colour: 'text-blue-600 bg-blue-100',
    draft_field: 'context_question_drafts',
    field: 'context_questions',
    attach: 'collaborate.corpus.work-expression.attach-context-questions',
    detach: 'collaborate.corpus.work-expression.detach-context-questions',
    apply: 'collaborate.corpus.work-expression.apply-context-questions',
  },
  legalDomains: {
    label: 'Legal Domains',
    icon: 'scale-balanced',
    colour: 'text-green-600 bg-green-100',
    draft_field: 'legal_domain_drafts',
    field: 'legal_domains',
    attach: 'collaborate.corpus.work-expression.attach-legal-domains',
    detach: 'collaborate.corpus.work-expression.detach-legal-domains',
    apply: 'collaborate.corpus.work-expression.apply-legal-domains',
  },
  locations: {
    label: 'Jurisdictions',
    icon: 'location-dot',
    colour: 'text-red-600 bg-red-100',
    draft_field: 'location_drafts',
    field: 'locations',
    attach: 'collaborate.corpus.work-expression.attach-locations',
    detach: 'collaborate.corpus.work-expression.detach-locations',
    apply: 'collaborate.corpus.work-expression.apply-locations',
  },
  tags: {
    label: 'Tags',
    icon: 'tags',
    colour: 'text-libryo-gray-600 bg-libryo-gray-100',
    draft_field: 'tag_drafts',
    field: 'tags',
    attach: 'collaborate.corpus.work-expression.attach-tags',
    detach: 'collaborate.corpus.work-expression.detach-tags',
    apply: 'collaborate.corpus.work-expression.apply-tags',
  },
};

const includeMap = {
  actionAreas: ['actionAreas', 'actionAreaDrafts'],
  assessmentItems: ['assessmentItems', 'assessmentItemDrafts'],
  categories: ['categories.categoryType', 'categoryDrafts.categoryType'],
  contextQuestions: ['contextQuestions', 'contextQuestionDrafts'],
  legalDomains: ['legalDomains', 'legalDomainDrafts'],
  locations: ['locations', 'locationDrafts'],
  tags: ['tags', 'tagDrafts'],
};
export const useMetaVisibility = () => {
  const [toc, setToc] = useState(false);
  const [sourceDocument, setSourceDocument] = useState(false);
  const [plainText, setPlainText] = useState(false);
  const [notes, setNotes] = useState(false);

  const meta = reactive({
    actionAreas: false,
    assessmentItems: false,
    categories: false,
    contextQuestions: false,
    legalDomains: false,
    locations: false,
    tags: false,
    toc: false,
    sourceDocument: false,
    plainText: false,
    notes: false,
  });

  function toggleMeta(item) {
    if (item === 'toc') {
      setToc(!toc.value);
      return;
    }
    if (item === 'sourceDocument') {
      setSourceDocument(!sourceDocument.value);
      setPlainText(false);
      return;
    }
    if (item === 'plainText') {
      setPlainText(!plainText.value);
      setSourceDocument(false);
      return;
    }
    if (item === 'notes') {
      setNotes(!notes.value);
      return;
    }

    meta[item] = !meta[item];
  }

  const mataIncludes = computed(() => {
    const params = [];

    Object.keys(meta).forEach((key) => {
      if (meta[key] && includeMap[key]) {
        includeMap[key].forEach((item) => params.push(item));
      }
    });

    return params.length > 0 ? { include: params.join('|') } : {};
  });

  return { meta, toc, sourceDocument, plainText, notes, mataIncludes, toggleMeta };
};
