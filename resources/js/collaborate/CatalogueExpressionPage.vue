<script>
import highlights from './mixins/highlights';
import TocContent from './components/TocContent.vue';
import TocReference from './components/ToCReference.vue';
import TocReferenceButtons from './components/TocReferenceButtons.vue';

export default {
  name: 'CatalogueExpressionPage',
  components: {
    TocContent,
    TocReference,
    TocReferenceButtons,
  },
  mixins: [highlights],
  data() {
    return {
      content: '',
      loading: false,
      unhiding: false,
      contentLoaded: false,
      selectedNodes: [],
      references: [],
      checked: [],
      containerId: 'toc-content',
      persist: {},
      isSection: false,
      original: {},
      volume: { current: 1, last: 1 },
      fetchingReferences: false,
    };
  },
  computed: {
    expressionId() {
      return parseInt(document.querySelector('#app').getAttribute('data-expression'), 10);
    },
  },
  created() {
    this.fetchExpression();

    const params = new URLSearchParams(window.location.search);

    if (params.has('volume')) {
      this.volume.current = parseInt(params.get('volume'), 10);
    }
  },
  methods: {
    fetchExpression() {
      this.volume.last = parseInt(document.querySelector('#app').getAttribute('data-last-volume'), 10);
    },

    updateExistingReference(existing, reference) {
      const updated = [...this.references];

      const index = updated.findIndex((item) => item.id === existing.id);

      updated[index] = {
        ...updated[index],
        ...reference,
        id: updated[index].id,
        start: updated[index].start || reference.start,
      };

      this.references = [...updated];
    },

    /* eslint-disable no-param-reassign */
    cleanText(text) {
      if (text) {
        text = text.replace(/\t/mg, ' ');
        text = text.replace(/(↵|\n|\r|\r\n)+/mg, ' ');
        text = text.replace(/(&nbsp;|\s)+/mg, ' ');
        text = text.replace(/\s/mg, '');
        text = text.replace(/[.()[\]]/mg, '');
        text = text.trim();
      }

      return text;
    },

    handleContentReferences(refs) {
      if (this.fetchingReferences) {
        setTimeout(() => this.handleContentReferences(refs), 300);
        return;
      }

      refs.forEach((reference) => {
        if (reference.selectors.length < 1) {
          return;
        }

        const quote = reference.selectors.filter((sel) => sel.type === 'TextQuoteSelector')[0];

        let extraOccurrences = refs.filter((item) => {
          if (reference.id === item.id) {
            return false;
          }

          const itemQuote = item.selectors.filter((sel) => sel.type === 'TextQuoteSelector')[0];

          return itemQuote && this.cleanText(itemQuote.exact) === this.cleanText(quote.exact);
        });

        const exactOccurrences = extraOccurrences.filter((item) => (
            item.selectors[2]
            && this.cleanText(item.selectors[2].prefix) === this.cleanText(reference.selectors[2].prefix)
            && this.cleanText(item.selectors[2].suffix) === this.cleanText(reference.selectors[2].suffix)
        )).length;

        extraOccurrences = extraOccurrences.length;

        let existing = this.references.find((item) => item.id === reference.id);

        if (existing) {
          this.updateExistingReference(existing, reference);
          return;
        }

        existing = this.references.filter((item) => {
          const itemQuote = item.selectors.filter((sel) => sel.type === 'TextQuoteSelector')[0];

          return itemQuote && this.cleanText(itemQuote.exact) === this.cleanText(quote.exact);
        });

        if (extraOccurrences < 1 && existing.length === 1) {
          this.updateExistingReference(existing[0], reference);
          return;
        }

        existing = existing.filter((item) => (
            item.selectors[2]
            && this.cleanText(item.selectors[2].prefix) === this.cleanText(reference.selectors[2].prefix)
            && this.cleanText(item.selectors[2].suffix) === this.cleanText(reference.selectors[2].suffix)
        ));

        if (exactOccurrences < 1 && existing.length === 1) {
          this.updateExistingReference(existing[0], reference);
          return;
        }

        existing = existing.filter((item) => {
          const range1 = item.selectors.filter((sel) => sel.type === 'RangeSelector')[0];
          const range2 = reference.selectors.filter((sel) => sel.type === 'RangeSelector')[0];

          return range1.startContainer === range2.startContainer && range1.endContainer === range2.endContainer;
        });

        if (existing.length === 1) {
          this.updateExistingReference(existing[0], reference);
          return;
        }

        this.persist[reference.id] = { ...reference };

        const index = this.references.findIndex((item) => item.start > reference.selectors[1].start);

        if (index !== -1) {
          const updated = [...this.references];
          updated.splice(index, 0, reference);
          this.references = [...updated];

          return;
        }

        this.references = [...this.references, reference];
      });

      this.references.forEach((item) => this.canAnchor(item));

      this.contentLoaded = true;
    },

    scrollToSection(ref) {
      this.$refs.content.scrollToSection(ref);
    },

    canAnchor(ref) {
      this.$refs.content
          .canAnchor(ref, (canAnchor) => {
            const index = this.references.findIndex((item) => item.id === ref.id);
            if (index) {
              const references = [...this.references];
              references[index] = { ...references[index], orphaned: !canAnchor };
              this.references = [...references];
            }
          });
    },
    changeVolume(page) {
      this.volume.current = page;
      this.$refs.content.$refs.content.scrollTo(0, 0);
    },
  },
};
</script>

<template>
  <div v-loading="loading || unhiding" class="flex flex-col overflow-hidden" style="height: calc(100vh - 7.5rem)">

    <div class="flex-grow flex overflow-auto custom-scroll">
      <div v-loading="!contentLoaded" class="flex flex-col w-1/2 flex-shrink-0 overflow-hidden h-full">

        <div v-if="contentLoaded" class="references w-full overflow-auto custom-scroll flex-grow pr-2">
          <toc-reference
              v-for="reference in references"
              :key="reference.id"
              :reference="reference"
              :checked="checked.includes(reference.id)"
              no-actions
              @click="() => scrollToSection(reference)"
          />
        </div>
      </div>

      <div class="h-full w-1/2 flex flex-col">
        <div class="flex-grow overflow-auto custom-scroll">
          <toc-content
              catalogue
              ref="content"
              :volume="volume.current"
              :last-volume="volume.last"
              @references="handleContentReferences"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.norma-legislation {
  font-size: 1rem;
  line-height: 2;
}
</style>

<style lang="scss">
.toc-editor {
  p, table {
    padding-left: 0.5rem;
    border-radius: 0.5rem;
  }
}
</style>
