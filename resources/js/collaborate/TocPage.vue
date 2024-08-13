<script>
import highlights from './mixins/highlights';
import TocContent from './components/TocContent.vue';
import TocReferenceTypeSelector from './components/TocReferenceTypeSelector.vue';
import TocReference from './components/ToCReference.vue';
import TocReferenceButtons from './components/TocReferenceButtons.vue';

export default {
  name: 'TocPage',
  components: {
    TocReferenceTypeSelector,
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
      completeChecked: [],
      containerId: 'toc-content',
      persist: {},
      isSection: false,
      original: {},
      volume: { current: 1, last: 1 },
      fetchingReferences: false,
    };
  },
  computed: {
    applyUpdate() {
      return document.querySelector('#app').getAttribute('data-apply-pending') == 1;
    },
    expressionId() {
      return parseInt(document.querySelector('#app').getAttribute('data-expression'), 10);
    },
    editable() {
      return true;
    },
    checkedReferences() {
      return this.checked.map((item) => this.references.find((ref) => ref.id === item));
    },
    canGenerate() {
      return document.querySelector('#app').getAttribute('data-generate') == 1;
    },
    canMerge() {
      return this.canGenerate && this.checked.length === 2 && !!this.checked.some((item) => item.toString().match(/[a-z]/i));
    },
    canDelete() {
      return document.querySelector('#app').getAttribute('data-delete') == 1;
    },
  },
  created() {
    this.fetchExpression();

    const params = new URLSearchParams(window.location.search);

    if (params.has('volume')) {
      this.volume.current = parseInt(params.get('volume'), 10);
    }

    this.init();
  },
  methods: {
    updateContent({ volume, content }) {
      return window.axios.put(`/toc/expressions/${this.expressionId}/volumes/${volume}/content`, content)
        .then(({ data }) => data);
    },

    destroy({ id }) {
      return window.axios.delete(`/toc/expressions/${this.expressionId}/references/${id}`)
        .then(({ data }) => data);
    },

    applyCitations() {
      return window.axios.put(`/toc/expressions/${this.expressionId}/references/apply`)
        .then(({ data }) => data);
    },

    init() {
      this.loading = true;
      this.fetchCitations()
        .then(() => {
          this.loading = false;
          return this.$nextTick();
        })
        .then(() => {
          this.references.forEach((item) => {
            new Promise((res) => {
              this.canAnchor(item);
              res(true);
            });
          });
        })
        .finally(() => {
          this.loading = false;
        });
    },

    fetchCitations() {
      if (this.unhiding) {
        return Promise.resolve();
      }

      this.fetchingReferences = true;
      const payload = {
        volume: this.volume.current,
        page: 0,
        perPage: 100,
        lastPage: 1,
      };

      this.references = [];

      return this.handleFetchReferences(payload)
        .then((data) => {
          this.references = [...data];
        })
        .finally(() => {
          this.fetchingReferences = false;
        });
    },

    fetchReferences(payload) {
      return window.axios.get(`/toc/expressions/${this.expressionId}/references/${payload.volume}?page=${payload.page}`).then(({ data }) => data);
    },

    handleFetchReferences(payload, current = []) {
      // eslint-disable-next-line no-param-reassign
      payload.page += 1;

      return this.$nextTick()
        .then(() => this.fetchReferences(payload))
        .then((data) => {
          const fetched = data.data.map((item) => {
            if (item.citation) {
              // eslint-disable-next-line
              item = {
                ...item,
                norm_type: item.citation.type,
              };
            }
            // eslint-disable-next-line
            delete item.citation;

            return item;
          });

          if (payload.page !== data.last_page) {
            return this.handleFetchReferences({
              ...payload,
              page: payload.page,
              lastPage: data.last_page,
            }, [...current, ...fetched]);
          }

          return [...current, ...fetched];
        });
    },

    fetchExpression() {
      this.volume.last = parseInt(document.querySelector('#app').getAttribute('data-last-volume'), 10);
    },

    handleSave({ content }) {
      this.loading = true;

      return this.updateContent({ volume: this.volume.current, content })
        .then(() => {
          if (!this.unhiding) {
            window.toast.success({ message: 'Saved successfully' });
          }
        })
        .catch(() => {
          window.toast.success({ message: 'Whoop! Something went wrong.' });
        })
        .finally(() => {
          this.loading = false;
        });
    },

    handleCheck(reference) {
      const index = this.checked.indexOf(reference.id);

      if (index === -1) {
        this.checked.push(reference.id);
        this.completeChecked.push(reference);
        return;
      }

      this.checked.splice(index, 1);
      this.completeChecked.splice(index, 1);
    },

    handleChangeProp(ref, dataProp, prop, value) {
      this.$refs.content.updateReferenceHTML(ref, dataProp, value);

      const index = this.references.findIndex((item) => item.id === ref.id);
      const update = { ...(index === -1 ? ref : this.references[index]), [prop]: value };

      if (index !== -1) {
        const allReferences = [...this.references];
        allReferences[index] = update;
        this.references = [...allReferences];
      }
    },

    handleIsSection(references, enable) {
      references.forEach((ref) => {
        this.handleChangeProp(ref, 'data-is_section', 'is_section', enable ? 1 : 0);
      });
    },

    getLevel(ref, increase) {
      let { level } = ref;

      if (Number.isNaN(level) || !level) {
        return increase ? 1 : ref.level;
      }

      level = increase ? level + 1 : level - 1;
      level = level < 1 ? 1 : level;

      return level;
    },

    handleLevel(references, increase) {
      references.forEach((ref) => {
        const level = this.getLevel(ref, increase);
        this.handleChangeProp(ref, 'data-level', 'level', level);
      });
    },

    handleCheckAllClick() {
      this.checked = this.checked.length === this.references.length
        ? []
        : this.references.map((item) => item.id);
    },

    handleToggleIsSection() {
      this.handleIsSection(this.checkedReferences, !this.isSection);
      this.isSection = !this.isSection;
    },

    handleType(references, type) {
      references.forEach((ref) => {
        this.handleChangeProp(ref, 'data-type', 'norm_type', type);
      });
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
          if (!item.ref_selector) {
            return;
          }
          if (!item.ref_selector.selectors) {
            return;
          }
          const itemQuote = item.ref_selector.selectors.filter((sel) => sel.type === 'TextQuoteSelector')[0];

          return itemQuote && this.cleanText(itemQuote.exact) === this.cleanText(quote.exact);
        });

        if (extraOccurrences < 1 && existing.length === 1) {
          this.updateExistingReference(existing[0], reference);
          return;
        }

        existing = existing.filter((item) => (
          item.ref_selector.selectors[2]
          && this.cleanText(item.ref_selector.selectors[2].prefix) === this.cleanText(reference.selectors[2].prefix)
          && this.cleanText(item.ref_selector.selectors[2].suffix) === this.cleanText(reference.selectors[2].suffix)
        ));

        if (exactOccurrences < 1 && existing.length === 1) {
          this.updateExistingReference(existing[0], reference);
          return;
        }

        existing = existing.filter((item) => {
          const range1 = item.ref_selector.selectors.filter((sel) => sel.type === 'RangeSelector')[0];
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

      setTimeout(() => {
        this.references.forEach((item) => this.canAnchor(item));
      }, 1000);

      this.contentLoaded = true;
    },

    scrollToSection(ref) {
      this.$refs.content.scrollToSection(ref);
    },

    handleSelectType(type) {
      const checked = [];
      this.references.forEach((ref) => {
        if (ref.norm_type === type) {
          checked.push(ref.id);
        }
      });

      this.checked = [...checked];
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
    handleDelete(reference) {
      this.destroy(reference)
        .then(() => {
          const index = this.references.findIndex((item) => item.id === reference.id);

          if (index !== -1) {
            let refs = [...this.references];
            refs.splice(index, 1);
            this.references = [...refs];
            refs = null;
          }

          this.$refs.content.generateReferences();
        });
    },
    changeVolume(page) {
      this.volume.current = page;
      this.$refs.content.$refs.content.scrollTo(0, 0);

      return this.fetchCitations();
    },
    handleApplyCitations() {
      this.$confirm({ title: 'Apply Citations', message: 'Are you sure you want apply all the citations in this work?' })
        .then(() => {
          this.loading = true;

          return this.applyCitations();
        })
        .then(() => {
          window.toast.success({ message: 'Successfully applied citations.' });
          return this.fetchCitations();
        })
        .finally(() => {
          this.loading = false;
        });
    },

    unhideRepealed(reset = false) {
      this.unhiding = true;

      if (reset && this.volume.current !== 1) {
        this.changeVolume(1);

        return Promise.resolve();
      }

      Array.from(document.querySelectorAll('[style*="display: none"] [data-type], [style*="display: none"][data-type]'))
        .forEach((node) => {
          const parent = node.closest('[style*="display: none"]');
          if (parent) {
            parent.style.display = null;
          }
        });

      return this.$nextTick()
        .then(() => this.handleSave(this.$refs.content.getSaveContent()))
        .then(() => new Promise((res) => setTimeout(res, 3000)))
        .then(() => {
          if (this.volume.current !== this.volume.last) {
            this.changeVolume(this.volume.current + 1);
            return;
          }

          window.toast.success({ message: 'Successfully processed text.' });

          this.unhiding = false;

          this.changeVolume(1);
        })
        .catch(() => {
          this.unhiding = false;
        });
    },

    proceedUnhiding() {
      if (this.unhiding) {
        this.$nextTick().then(() => this.unhideRepealed());
      }
    },

    mergeReferences() {
      const newRef = this.completeChecked.filter((ref) => ref.id.toString().match(/[a-z]/i))[0];
      const oldRef = this.completeChecked.filter((ref) => !ref.id.toString().match(/[a-z]/i))[0];

      const payload = {
        ...oldRef,
        selectors: newRef.selectors,
        text: newRef.text,
        start: newRef.start,
        level: newRef.level,
        is_section: newRef.is_section,
        title: `${newRef.number || ''} ${newRef.heading}`.trim(),
        volume: newRef.volume,
      };

      this.$confirm({ title: 'Merge Citations', message: 'Are you sure you want to update the citation?' })
        .then(() => {
          this.loading = true;

          return window.axios.put(`/toc/expressions/${this.expressionId}/references/${oldRef.id}`, payload);
        })
        .then(({ data }) => {
          window.toast.success({ message: 'Successfully updated citation.' });
          const refs = [...this.references];
          const newRefIndex = refs.findIndex((item) => item.id === newRef.id);

          if (newRefIndex !== -1) {
            refs.splice(newRefIndex, 1);
          }

          const oldRefIndex = refs.findIndex((item) => item.id === oldRef.id);

          if (oldRefIndex !== -1) {
            refs.splice(oldRefIndex, 1, data.data);
          }

          this.references = [...refs];
          this.checked = [];
          this.completeChecked = [];
        })
        .finally(() => {
          this.loading = false;
        });
    },
    handleDeleteCitations() {
      const payload = {
        '_method': 'DELETE',
        data: this.checked.filter((item) => !item.toString().match(/[a-z]/gi)),
      };

      if (payload.data.length < 1) {
        window.toast.error({ message: 'Please select existing references.' });
        return;
      }

      this.$confirm({ title: 'Merge Citations', message: 'Are you sure you want to delete the citations?' })
        .then(() => {
          this.loading = true;
          return window.axios.post(`/toc/expressions/${this.expressionId}/references/bulk`, payload);
        })
        .then(() => {
          const refs = [...this.references];
          payload.data.forEach((item) => {
            const index = refs.findIndex((pre) => pre.id === item);
            if (index !== -1) {
              refs.splice(index, 1);
            }
          });
          this.checked = [];
          this.completeChecked = [];
          this.references = [...refs];
          window.toast.success({ message: 'Successfully deleted references.' });
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
};
</script>

<template>
  <div v-loading="loading || unhiding" class="flex flex-col overflow-hidden h-full pb-1">
    <div class="flex-grow flex overflow-hidden pb-1">
      <div v-loading="!contentLoaded" class="flex flex-col w-1/2 flex-shrink-0 overflow-hidden h-full">
        <div class="actions flex flex-shrink-0 py-2 border-b border-gray-200 justify-between pl-4">
          <div class="flex items-center flex-grow">
            <input
              class="mr-2 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
              type="checkbox"
              :checked="checked.length === references.length"
              @click.stop="handleCheckAllClick"
            >

            <toc-reference-buttons
              :is-section="isSection"
              @section="handleToggleIsSection"
              @indent="() => handleLevel(checkedReferences, true)"
              @outdent="() => handleLevel(checkedReferences, false)"
              @type="(e) => handleType(checkedReferences, e)"
            />

            <toc-reference-type-selector class="ml-1" label="Select of type" @change="handleSelectType" />

            <button
              v-if="canMerge"
              type="button"
              class="btn btn-outline-secondary ml-2"
              style="border-radius:0.25rem;border:1px solid"
              @click.stop.prevent="mergeReferences"
            >
              <span class="mr-1">Merge References</span>
            </button>
          </div>

          <div class="flex items-center space-x-2">
            <button
              v-if="canDelete && checked.length > 0"
              class="btn btn-outline-danger btn-sm"
              @click="handleDeleteCitations"
            >
              Delete
            </button>
            <button v-if="applyUpdate" class="btn btn-outline-primary btn-sm" @click="handleApplyCitations">
              Apply All
            </button>
          </div>
        </div>

        <div v-if="contentLoaded" class="references w-full overflow-auto custom-scroll flex-grow pr-2">
          <toc-reference
            v-for="reference in references"
            :key="reference.id"
            :reference="reference"
            :checked="checked.includes(reference.id)"
            @check="() => handleCheck(reference)"
            @type="(e) => handleType([reference], e)"
            @section="(e) => handleIsSection([reference], e)"
            @indent="() => handleLevel([reference], true)"
            @outdent="() => handleLevel([reference], false)"
            @click="() => scrollToSection(reference)"
            @delete="handleDelete"
          />
        </div>
      </div>

      <div class="h-full w-1/2 flex flex-col">
        <div class="flex-grow overflow-auto custom-scroll">
          <toc-content
            ref="content"
            :volume="volume.current"
            :last-volume="volume.last"
            unhide
            :unhiding="unhiding"
            @save="handleSave"
            @references="handleContentReferences"
            @unhide="() => unhideRepealed(true)"
            @fetched="proceedUnhiding"
          />
        </div>

        <div class="flex-shrink-0">
          <pagination :current="volume.current" :per-page="1" :total="volume.last" @page="changeVolume" />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.libryo-legislation {
  font-size: 1rem;
  line-height: 2;
}
</style>

<style lang="scss">
.toc-editor {

  p,
  table {
    padding-left: 0.5rem;
    border-radius: 0.5rem;
  }
}
</style>
