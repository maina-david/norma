<script>
import cuid from 'cuid';
import { uniq } from 'lodash';
import highlighter from '../libryo-highlighter';
import Highlighter from '../highlighter';
import checkVolumes from '../mixins/validate_volumes';
import styles from '../mixins/styles';
import highlight from '../mixins/highlight';
import Tagger from '../mixins/tagger';

export default {
  name: 'TocContent',
  mixins: [checkVolumes, styles],
  props: {
    catalogue: { type: Boolean, default: false },
    noReferences: { type: Boolean, default: false },
    noGeneration: { type: Boolean, default: false },
    unhide: { type: Boolean, default: false },
    unhiding: { type: Boolean, default: false },
    withPreviewReferences: { type: Boolean, default: false },
    externalContent: { type: String, default: null },
    volume: { type: [Number, String], required: true },
    lastVolume: { type: [Number, String], required: true },
  },
  data() {
    return {
      generatingPercentage: null,
      dmp: null,
      content: this.externalContent,
      selectorPosition: { top: 0, left: 0 },
      showHighlightSelector: false,
      type: 'part',
      levels: ['part'],
      level: 1,
      loading: false,
      previousSpan: null,
      previousSpanType: null,
      highlightListener: null,
      metaListener: false,
      shortcutListener: null,
      unloadListener: null,
      associatePrevious: false,
      selectedSpan: null,
      isDirty: false,
      source: null,
      withoutIds: [],
      anchorRequests: [],
      testingAnchoring: false,
      regex: '^ *(\\d+[A-Z]?)\\s*([A-Z].+)$',
      multiSelect: false,
      multiSelectedSpans: [],
    };
  },
  computed: {
    canGenerate() {
      return document.querySelector('#app').getAttribute('data-generate') == 1;
    },
    expressionId() {
      return parseInt(document.querySelector('#app').getAttribute('data-expression'), 10);
    },
  },
  watch: {
    volume() {
      this.init();
    },
  },
  mounted() {
    this.$nextTick().then(() => {
      this.init();

      if (!this.catalogue) {
        this.setupHighlighter();
        this.setupShortcuts();
        this.setupUnload();
      }

      this.checkGenerateProgress();
    });
  },
  beforeUnmount() {
    window.removeEventListener('keyup', this.shortcutListener);
    window.removeEventListener('keydown', this.metaListener);
  },
  methods: {
    retryCheckGenerateProgress() {
      setTimeout(() => this.checkGenerateProgress(), 10000);
    },
    checkGenerateProgress() {
      if (this.noGeneration || !this.canGenerate) {
        return;
      }

      window.axios.get(`/toc/expressions/${this.expressionId}/references/generate/status`)
        .then(({ data }) => data)
        .then(({ data }) => {
          if (data.retry) {
            this.generatingPercentage = 0;
            this.retryCheckGenerateProgress();
            return;
          }

          if (!data.id || data.cancelledAt) {
            this.generatingPercentage = null;

            return;
          }

          if (data.finishedAt || data.progress > 99) {
            this.generatingPercentage = null;
            window.toast.success({ message: 'Citation generation completed.' });

            return;
          }

          this.generatingPercentage = data.progress;

          this.retryCheckGenerateProgress();
        });
    },

    validateVolumeMarkers() {
      return window.axios.post(`/toc/expressions/${this.expressionId}/references/volumes/validate`, {});
    },

    generateCitations() {
      return window.axios.post(`/toc/expressions/${this.expressionId}/references/generate`, {});
    },

    fetchRawContent({ volume }) {
      return window.axios.get(`/toc/expressions/${this.expressionId}/content/${volume}`).then(({ data }) => data);
    },

    getDocument() {
      return window.axios.get(`/catalogue-work-expressions/${this.expressionId}/content`).then(({ data }) => data);
    },

    handleGenerateCitations() {
      this.errors = [];
      this.loading = true;
      this.validateVolumeMarkers()
        .then(({ data }) => {
          if (data.invalid.length < 1) {
            return this.validateVolumes();
          }

          this.$confirm({ message: `We are unable to generate citations. These volumes are invalid: ${data.invalid.join(', ')}.`, confirm: 'Okay' });
          throw new Error();
        })
        .then(() => this.$confirm({ message: 'Are you sure you want to generate citations for the document?', confirm: 'Generate' }))
        .then(() => {
          this.loading = true;
          return this.generateCitations();
        })
        .then(() => {
          window.toast.success({ message: 'Successfully requested citations.' });
          this.generatingPercentage = 0;
          setTimeout(() => this.checkGenerateProgress(), 100000);
        })
        .finally(() => {
          this.loading = false;
        })
        .catch(() => {
        });
    },

    init() {
      if (this.externalContent) {
        this.content = this.cleanContent(this.externalContent);
        this.$nextTick().then(() => {
          this.generateReferences().then((res) => this.$emit('references', res));
        });

        return;
      }

      this.fetchContent();
    },

    fetchLevelsWithNoId() {
      this.withoutIds = Array.from(this.$refs.content.querySelectorAll(
        '[data-level][data-inline="num"]:not([data-id]),[data-level] > [data-inline="num"]:not([data-id])',
      ));

      return this.withoutIds;
    },

    generateReferences() {
      if (this.noReferences) {
        return Promise.resolve([]);
      }

      return this.handleReferenceGeneration();
    },

    handleReferenceGeneration() {
      const foundIds = uniq(
        Array.from(this.$refs.content.querySelectorAll('[data-level][data-id], [data-level] > [data-id], [data-level] > * > [data-id]'))
          .map((item) => item.getAttribute('data-id')),
      );

      const filterItems = (commonId) => !!commonId && this.$refs
        .content
        .querySelectorAll(`[data-inline="num"][data-id="${commonId}"],[data-inline="heading"][data-id="${commonId}"]`)
        .length > 0;

      return Promise.all(foundIds.filter(filterItems)
        .map((commonId) => ({
          ...highlight.generateSelections(commonId),
          volume: this.volume,
        })));
    },

    handleSave(autosave = false) {
      this.$emit('save', this.getSaveContent(autosave));
    },

    getSaveContent(autosave = false) {
      this.removeSelectedClass();

      const original = this.$refs.content.innerHTML;

      const content = { content: this.cleanStyles(this.$refs.content).innerHTML, autosave };

      this.$nextTick(() => {
        this.$refs.content.innerHTML = original;
      });

      return content;
    },

    fetchRelativeContent(payload) {
      return this.catalogue
        ? this.getDocument(payload)
        : this.fetchRawContent(payload);
    },

    /* eslint-disable no-param-reassign */
    cleanContent(content) {
      content = content.replace(/&nbsp;/gim, ' ')
        .replace(/text-indent:\s*-(\d+\w+;)?/g, 'text-indent: $1')
        .replace(/href=(["'])[^"']*\/files\//g, 'href=$1https://my.libryo.com/files/');

      content = (new DOMParser()).parseFromString(content, 'text/html');
      content.querySelectorAll('.pc').forEach((item) => item.classList.add('opened'));
      Array.from(content.head.children).reverse().forEach((child) => {
        const exclude = ['META', 'TITLE'];

        if (!exclude.includes(child.nodeName)) {
          content.body.prepend(child);
        }
      });

      this.cleanStyles(content.body);
      this.constrainStyles(content.body);

      return content.body.innerHTML;
    },

    fetchContent() {
      this.loading = true;
      return this.fetchRelativeContent({ volume: this.volume })
        .then((data) => {
          this.content = this.cleanContent(data);

          return data;
        })
        .finally(() => {
          this.loading = false;

          if (this.unhiding) {
            this.$emit('fetched');
            return;
          }

          this.$nextTick().then(() => {
            this.generateReferences().then((res) => this.$emit('references', res));
            this.fetchLevelsWithNoId();
          });
        });
    },

    setupHighlighter() {
      this.highlightListener = highlighter.registerSelectionListener(
        this.$refs.content,
        () => {},
        (e) => {
          this.$nextTick()
            .then(() => {
              const range = window.getSelection().getRangeAt(0);

              if (range.collapsed) {
                window.getSelection().removeAllRanges();
                return;
              }
              const left = window.innerWidth - 200 < e.pageX ? window.innerWidth - 200 : e.pageX;

              this.selectorPosition = { top: `${e.pageY}px`, left: `${left}px` };

              if (!this.associatePrevious || !this.previousSpan) {
                this.showHighlightSelector = true;
                return;
              }

              this.setSelection(this.previousSpanType === 'num' ? 'heading' : 'num');
            });
        },
      );
    },

    setupShortcuts() {
      this.shortcutListener = (e) => {
        if (e.key.match(/^(control|meta)/i)) {
          if (this.previousSpan) {
            this.previousSpan.classList.remove('link-to');
          }
          return;
        }

        if (e.key.match(/^delete/i)) {
          this.handleDelete();
          return;
        }

        if (e.key.match(/^[nh]$/i) && this.showHighlightSelector) {
          this.setSelection({ n: 'num', h: 'heading' }[e.key.toString()]);
          return;
        }

        if (!e.key.match(/^[acps1234]$/i)) {
          return;
        }

        e.preventDefault();

        const types = {
          a: 'article',
          c: 'chapter',
          p: 'part',
          s: 'section',
          1: this.type,
          2: this.type,
          3: this.type,
          4: this.type,
        };

        this.setType(types[e.key.toLowerCase()]);

        const key = parseInt(e.key, 10);

        this.level = Number.isNaN(key) ? this.levels.indexOf(this.type) + 1 : key;
      };
      this.metaListener = (e) => {
        if (e.key.match(/^(control|meta)/i)) {
          if (this.previousSpan) {
            this.previousSpan.classList.add('link-to');
          }
        }
      };
      window.addEventListener('keydown', this.metaListener);
      window.addEventListener('keyup', this.shortcutListener);
    },

    setupUnload() {
      this.unloadListener = (event) => {
        if (this.isDirty) {
          event.preventDefault();
          // eslint-disable-next-line no-param-reassign
          event.returnValue = '';
        }
      };

      window.addEventListener('beforeunload', this.unloadListener);
    },

    setType(type) {
      if (this.type !== type) {
        const index = this.levels.indexOf(type);
        if (index !== -1) {
          this.levels.splice(index, this.levels.length);
        }
        this.levels.push(type);
      }

      this.type = type;
      this.level = this.levels.indexOf(this.type) + 1;
    },

    setSelection(type) {
      const span = document.createElement('span');
      span.setAttribute('data-level', this.level);
      span.setAttribute('data-type', this.type);
      span.setAttribute('data-id', cuid.slug());
      span.setAttribute('data-inline', type);

      if (this.type === 'section') {
        span.setAttribute('data-is_section', 1);
      }

      // TODO: add a warning if trying to associate two nums or headings
      if (this.associatePrevious && this.previousSpan && this.previousSpan.getAttribute('data-type') !== type) {
        span.setAttribute('data-id', this.previousSpan.getAttribute('data-id'));
        span.removeAttribute('data-level');
        span.removeAttribute('data-type');
        span.removeAttribute('data-is_section');
      }

      const range = window.getSelection().getRangeAt(0);

      this.removeSelectedClass();

      span.appendChild(range.extractContents());
      range.insertNode(span);

      window.getSelection().removeAllRanges();

      this.previousSpan = span;
      this.previousSpanType = type;
      this.showHighlightSelector = false;

      this.generateReferences().then((res) => this.$emit('references', res));
    },

    handleMouseDown(e) {
      this.showHighlightSelector = false;
      this.associatePrevious = e.ctrlKey || e.metaKey;
    },

    clearSuggestions() {
      this.levels = [];
      this.type = null;
    },

    handleContentClick(e) {
      const span = e.target.closest('[data-inline="num"],[data-inline="heading"]');

      if (!window.getSelection().isCollapsed || !span) {
        return;
      }

      if (span.classList.contains('selected')) {
        this.selectedSpan = null;

        if (!this.multiSelect) {
          document.querySelectorAll('.selected').forEach((item) => item.classList.remove('selected'));
          return;
        }

        const index = this.multiSelectedSpans.indexOf(span);

        if (index !== -1) {
          span.classList.remove('selected');
          this.multiSelectedSpans.splice(index, 1);
        }

        return;
      }

      this.selectedSpan = span;
      this.previousSpan = span;
      this.previousSpanType = span.getAttribute('data-inline');

      if (this.multiSelect) {
        this.selectedSpan.classList.add('selected');
        const index = this.multiSelectedSpans.indexOf(span);

        if (index === -1) {
          this.multiSelectedSpans.push(span);
        }

        return;
      }

      document.querySelectorAll('.selected').forEach((item) => item.classList.remove('selected'));
      this.selectedSpan.classList.add('selected');
    },

    removeSelectedClass() {
      document.querySelectorAll('.selected, .link-to').forEach((item) => {
        item.classList.remove('selected');
        item.classList.remove('link-to');

        if (item.classList.length === 0) {
          item.removeAttribute('class');
        }
      });
    },

    handleDelete() {
      if (!this.selectedSpan || (this.multiSelect && this.multiSelectedSpans.length < 1)) {
        return;
      }

      this.$confirm({ title: 'Delete', message: 'Are you sure you want to delete?', confirm: 'Delete' })
        .then(() => {
          this.removeSelectedClass();

          if (!this.multiSelect && this.selectedSpan) {
            this.selectedSpan.parentElement.innerHTML = this.selectedSpan.parentElement.innerHTML.replace(
              this.selectedSpan.outerHTML,
              this.selectedSpan.innerHTML,
            );

            this.selectedSpan = null;

            return;
          }

          this.multiSelectedSpans.forEach((span) => {
            try {
              span.parentElement.innerHTML = span.parentElement.innerHTML.replace(
                span.outerHTML,
                span.innerHTML,
              );
            } catch (e) {
            }
          });

          this.generateReferences().then((res) => this.$emit('references', res));
          this.selectedSpan = null;
          this.multiSelectedSpans = [];
        });
    },

    updateReferenceHTML(reference, attribute, value) {
      return Highlighter.anchor(this.$refs.content, reference.selectors || reference.ref_selector)
        .then((range) => {
          if (!range) {
            return null;
          }

          let container;

          if (range.startContainer.getAttribute) {
            const commonId = range.startContainer.getAttribute('data-id');
            if (commonId) {
              container = document.querySelector(`[data-id="${commonId}"][data-level]`);
            }
          }

          if (!container) {
            container = range.startContainer.nodeType === Node.TEXT_NODE
              ? range.startContainer.parentElement.closest('[data-type],[data-level]')
              : range.startContainer.closest('[data-type],[data-level]');
          }

          if (!container) {
            container = range.startContainer.nodeType === Node.TEXT_NODE
              ? range.startContainer.parentElement.closest('[data-id][data-inline]')
              : range.startContainer.closest('[data-id][data-inline]');

            if (container) {
              const commonId = container.getAttribute('data-id');
              container = document.querySelector(`[data-id="${commonId}"][data-level]`);
            }
          }

          if (!container) {
            return container;
          }

          if (typeof value === 'function') {
            // eslint-disable-next-line no-eval,no-param-reassign
            value = value(reference, container);
          }

          if (!value) {
            container.removeAttribute(attribute);
            return container;
          }

          container.setAttribute(attribute, value);

          return container;
        });
    },

    canAnchor(reference, callback) {
      this.anchorRequests.push({ reference, callback });
      this.testAnchoring();
    },

    testAnchoring() {
      if (this.anchorRequests.length < 1) {
        return;
      }

      if (this.loading || this.testingAnchoring) {
        setTimeout(() => this.testAnchoring(), 300);
        return;
      }

      const request = this.anchorRequests.pop();

      if (!request.reference.ref_selector || !request.reference.ref_selector.selectors) {
        return;
      }

      this.testingAnchoring = true;

      Highlighter.anchor(this.$refs.content, request.reference.ref_selector.selectors)
        .then((range) => request.callback(!!range))
        .catch(() => request.callback(false))
        .finally(() => {
          this.testingAnchoring = false;
          this.testAnchoring();
        });
    },

    scrollToSection(reference) {
      return Highlighter.anchor(this.$refs.content, reference.selectors || reference.ref_selector.selectors)
        .then((range) => {
          if (range) {
            const start = range.startContainer.nodeType === Node.TEXT_NODE
              ? range.startContainer.parentElement
              : range.startContainer;

            start.scrollIntoView();
          }
        });
    },

    handleAttemptIdRecovery() {
      this.withoutIds.forEach((num) => {
        const genId = cuid.slug();

        num.setAttribute('data-id', genId);

        const heading = num.nextElementSibling;

        if (heading && heading.getAttribute('data-inline') === 'heading') {
          heading.setAttribute('data-id', genId);
        }
      });

      this.generateReferences().then((res) => this.$emit('references', res));

      this.fetchLevelsWithNoId();
    },
    addNumHeading() {
      const tagger = new Tagger();
      this.loading = true;
      const isSection = this.type === 'section';

      const tick = () => this.$nextTick();

      return tagger.tagElement(this.$refs.content, this.type, this.level, isSection, new RegExp(this.regex, 'gm'), tick)
        .then(() => {
          this.generateReferences().then((res) => this.$emit('references', res));
        })
        .finally(() => {
          this.loading = false;
        });
    },
    handlePreviewReferences() {
      this.$emit('loading-references', true);
      this.loading = true;
      setTimeout(() => {
        this.$nextTick()
          .then(() => this.handleReferenceGeneration())
          .then((refs) => {
            this.$emit('references', refs);
          })
          .finally(() => {
            this.loading = false;
            this.$emit('loading-references', false);
          });
      }, 200);
    },

    scrollToError(err) {
      this.errorsVisible = false;
      // eslint-disable-next-line eqeqeq
      if (err.volume != this.volume) {
        this.$parent.volume = { ...this.$parent.volume, current: err.volume };
        setTimeout(() => this.showDocumentSelector(err.selector), 2000);
        return;
      }

      this.$nextTick().then(() => this.showDocumentSelector(err.selector));
    },

    showDocumentSelector(selector, tries = 1) {
      if (this.loading) {
        setTimeout(() => this.showDocumentSelector(selector), 300);
        return;
      }

      const item = document.querySelector(selector);

      if (!item) {
        if (tries < 4) {
          setTimeout(() => this.showDocumentSelector(selector, tries + 1), 300 * tries);
        }
        return;
      }

      item.scrollIntoView({ behavior: 'smooth' });
      item.classList.add('blink');
      setTimeout(() => item.classList.remove('blink'), 2000);
    },
    unhideRepealed() {
      this.$confirm({ title: 'Show Repealed Text', message: 'This process takes a few minutes and shows the hidden repealed text. Continue?' })
        .then(() => this.$emit('unhide'))
        .catch(() => {});
    },
    toggleBulkSelect() {
      document.querySelectorAll('.selected').forEach((item) => item.classList.remove('selected'));
      this.multiSelect = !this.multiSelect;
      this.multiSelectedSpans = [];
    },
  },
};
</script>

<template>
  <div v-loading="loading" class="flex flex-col h-full relative ali">
    <div v-if="validating" class="validating">
      Please wait... Validating volume {{ currentValidated }} / {{ lastVolume }}
    </div>
    <div v-if="!catalogue" class="action-buttons flex items-end notranslate">
      <div class="flex-shrink-0">
        <div class="flex flex-col">
          <div class="btn-group" role="group">
            <button
              data-toggle="tooltip"
              title="Article"
              type="button"
              class="btn"
              :class="{'btn-outline-secondary': type !== 'article', 'border border-primary text-white bg-primary': type === 'article'}"
              @click.stop="() => setType('article')"
            >
              {{ type === 'article' ? 'Article' : 'A' }}
            </button>
            <button
              data-toggle="tooltip"
              title="Chapter"
              type="button"
              class="btn"
              :class="{'btn-outline-secondary': type !== 'chapter', 'border border-primary text-white bg-primary': type === 'chapter'}"
              @click.stop="() => setType('chapter')"
            >
              {{ type === 'chapter' ? 'Chapter' : 'C' }}
            </button>
            <button
              data-toggle="tooltip"
              title="Part"
              type="button"
              class="btn"
              :class="{'btn-outline-secondary': type !== 'part', 'border border-primary text-white bg-primary': type === 'part'}"
              @click.stop="() => setType('part')"
            >
              {{ type === 'part' ? 'Part' : 'P' }}
            </button>
            <button
              data-toggle="tooltip"
              title="Section"
              type="button"
              class="btn"
              :class="{'btn-outline-secondary': type !== 'section', 'border border-primary text-white bg-primary': type === 'section'}"
              @click.stop="() => setType('section')"
            >
              {{ type === 'section' ? 'Section' : 'S' }}
            </button>
            <button
              data-toggle="tooltip"
              title="Clear the level suggestion."
              type="button"
              class="btn btn-outline-secondary"
              @click.stop="clearSuggestions"
            >
              <span>
                <libryo-icon name="trash-alt" icon-size="sm" />
              </span>
            </button>
          </div>

          <div class="btn-group mt-1" role="group">
            <button
              v-for="it in [1,2,3,4,5,6]"
              :key="it"
              data-toggle="tooltip"
              title="Change the level for the new highlights"
              type="button"
              class="btn"
              :class="{'btn-outline-secondary': level !== it, 'border border-primary text-white bg-primary': level === it}"
              @click.stop="level = it"
            >
              {{ it }}
            </button>
          </div>
        </div>
      </div>
      <div class="flex items-end ml-2 flex-grow justify-between">
        <div class="flex ml-1 flex-col">
          <button v-if="withoutIds.length > 0" type="button" class="ml-4 mb-1 btn btn-sm btn-outline-secondary py-1" @click="handleAttemptIdRecovery">
            Attempt ID Recovery
          </button>
          <div class="flex items-center" style="display:none !important">
            <input v-model="regex" type="text" class="form-control">
            <button :disabled="!regex || regex.length < 1" type="button" class="ml-2 btn btn-outline-secondary py-1 btn-sm" @click.stop="addNumHeading">
              Attempt
            </button>
          </div>

          <div class="flex" style="margin-bottom:2px">
            <button type="button" class="btn py-1 btn-sm" :class="{'btn-outline-secondary': !multiSelect, 'bg-negative text-white': multiSelect }" @click.stop="toggleBulkSelect">
              {{ multiSelect ? 'Disable ': 'Enable ' }}
              Bulk Select
            </button>
          </div>

          <div v-if="unhide" class="flex" style="margin-bottom:2px">
            <button type="button" class="btn btn-outline-secondary py-1 btn-sm" @click.stop="unhideRepealed">
              Show Repealed Text
            </button>
          </div>
        </div>

        <div class="flex-shrink-0 flex flex-col">
          <div class="flex">
            <button v-if="withPreviewReferences" type="button" class="ml-4 mb-1 btn btn-sm btn-outline-secondary py-1" @click="handlePreviewReferences">
              Preview Citations
            </button>
            <div class="btn-group ml-4 mb-1" role="group">
              <button
                v-if="errors.length > 0"
                data-toggle="tooltip"
                title="Show Errors"
                type="button"
                class="btn btn-sm btn-danger py-1"
                @click.stop="errorsVisible = !errorsVisible"
              >
                <libryo-icon style="color: #fff" name="exclamation-circle" icon-size="lg" />
              </button>

              <template v-if="!noGeneration && canGenerate">
                <button v-if="generatingPercentage === null" type="button" class="btn btn-sm btn-outline-secondary" @click="handleGenerateCitations">
                  Generate Citations
                </button>
                <button v-else type="button" class="btn btn-sm btn-outline-secondary">
                  <span>Generating Citations</span>
                  <span class="ml-2">- {{ generatingPercentage }}% Done</span>
                </button>
              </template>
            </div>
          </div>

          <button type="button" class="btn btn-sm btn-outline-secondary py-1 ml-4 flex justify-center" @click="() => handleSave(false)">
            <span>Save</span>
          </button>
        </div>
      </div>
    </div>

    <div
      id="toc-content"
      ref="content"
      class="wysiwyg-content relative w-full libryo-legislation toc-editor lora px-4 overflow-auto custom-scroll h-full"
      style="scroll-behavior: smooth;"
      :class="{ associate: associatePrevious }"
      @mousedown="handleMouseDown"
      @click="handleContentClick"
      v-html="content"
    />

    <div v-if="errorsVisible" style="border-left:1px solid #ececec;position: absolute;right: 0;top: 0;height: 100%;width: 50%;z-index: 999;background: #fff;">
      <div class="flex justify-between m-2 border-b border-gray-200">
        <div class="pl-2 pt-2 fw-bold">
          Document Errors
        </div>
        <button type="button" class="btn btn-sm" style="padding:0.5rem;" @click.stop="errorsVisible = false">
          <libryo-icon name="times-circle" />
        </button>
      </div>

      <div class="m-2 px-2 border-b border-gray-200">
        <div v-for="(err,index) in errors" :key="index">
          <a class="flex-col flex text-decoration-none" href="#" @click.prevent="() => scrollToError(err)">
            <span class="error-heading">{{ err.text }}</span>
            <span style="font-size:0.8rem;">Volume {{ err.volume }}</span>
            <span style="font-size:0.9rem;color:red;">{{ err.error }}</span>
          </a>
        </div>
      </div>
    </div>

    <div v-if="!catalogue && showHighlightSelector" class="fixed p-2 bg-white" :style="selectorPosition" style="z-index: 99999;">
      <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-secondary btn-sm" style="padding:0 0.8rem;" @click.stop="() => setSelection('num')">
          Number
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" style="padding:0 0.8rem;" @click.stop="() => setSelection('heading')">
          Heading
        </button>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.validating {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 99999;
  font-size: 1.5rem;
  font-weight: 600;
  background: rgba(0, 0,0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100vw;
  height: 100vh;
  color: #fff;
}
.error-heading {
  white-space: nowrap;
  overflow: hidden;
  width: 100%;
  display: block;
  text-overflow: ellipsis;
}

.action-buttons {
  flex-shrink: 0;
  padding: 0.5rem;
}

#toc-content {
  flex-grow: 1;
  font-size: 1.25rem;
  line-height: 2;

  img {
    pointer-events: none;
  }

  .selected {
    border: 2px dotted red;
  }

  .link-to {
    border: 3px solid blue !important;
  }

  [data-is_section] {
    background: rgba(0, 0, 0, 0.1);
  }

  [data-type], [data-type] {
    position: relative;
  }

  [data-type]:before,
  [data-level]:after {
    display: inline-block;
    font-size: 0.8rem;
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    position: absolute;
  }

  [data-type]:before, [data-type]:before {
    content: attr(data-type);
    color: #be9518;
    top: -0.5rem;
    padding-left: 1rem;
    padding-right: 1rem;
    background: #f9fafb;
  }

  [data-level]:after, [data-level]:after {
    content: attr(data-level);
    color: #63b5c5;
    left: 0rem;
    top: -0.5rem;
    background: #f9fafb;
  }

  [data-inline="num"] {
    background: #b7e9c1;
    display: inline-block;
    padding-top: 0.7rem;
  }

  [data-inline="heading"] {
    background: #fdeab2;
    display: inline-block;
    padding-top: 0.7rem;
  }
}

.associate {
  cursor: copy;
}
</style>
