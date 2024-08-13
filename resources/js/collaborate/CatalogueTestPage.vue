<script>
import TocContent from './components/TocContent.vue';

export default {
  name: 'TestExpression',
  components: { TocContent },
  data() {
    return {
      loading: false,
      references: [],
      content: null,
    };
  },
  methods: {
    scrollToSection(ref) {
      this.$refs.content.scrollToSection(ref);
    },

    handleContentReferences(refs) {
      refs.forEach((reference) => {
        if (reference.selectors.length < 1) {
          return;
        }

        let existing = this.references.find((item) => item.id === reference.id);

        if (existing) {
          this.updateExistingReference(existing, reference);
          return;
        }

        existing = this.references.filter((item) => item.text === reference.selectors[2].exact);

        if (existing.length === 1) {
          this.updateExistingReference(existing[0], reference);
          return;
        }

        existing = existing.filter((item) => item.selectors[2].prefix === reference.selectors[2].prefix
            && item.selectors[2].suffix === reference.selectors[2].suffix);

        if (existing.length === 1) {
          this.updateExistingReference(existing[0], reference);
          return;
        }

        const index = this.references.findIndex((item) => item.start > reference.selectors[1].start);

        if (index !== -1) {
          const updated = [...this.references];
          updated.splice(index, 0, reference);
          this.references = [...updated];

          return;
        }

        this.references = [...this.references, reference];
      });
    },

    handleUpload(e) {
      this.loading = true;
      this.content = null;
      this.references = [];
      this.$nextTick()
          .then(() => {
            const reader = new FileReader();
            reader.addEventListener('load', (event) => {
              this.content = event.target.result;
              this.loading = false;
            });
            reader.readAsText(e.target[0].files[0]);
          });
    },
  },
};
</script>

<template>
  <div v-loading="loading" class="h-full flex flex-col overflow-hidden">
    <div class="flex py-2 items-center border-b border-gray-200 px-2">
      <form action="#" @submit.prevent="handleUpload">
        <input
            required
            accept="text/html"
            type="file"
            name="upload"
            class="form-control-sm"
        >
        <button class="btn btn-sm btn-outline-primary">
          Test selected file
        </button>
      </form>
    </div>

    <div class="flex-grow flex overflow-auto">
      <div class="flex flex-col w-1/4 flex-shrink-0 overflow-hidden h-full">
        <div class="references w-full overflow-auto flex-grow custom-scroll">
          <div
              v-for="reference in references"
              :key="reference.id"
              class="ref-item"
              :style="`margin-left:${reference.level}rem`"
              @click="() => scrollToSection(reference)"
          >
            <span v-if="reference.is_section" class="bg-primary text-white py-1 px-2 rounded">§</span>
            {{ reference.number }}
            {{ reference.heading }}
          </div>
        </div>
      </div>

      <div class="h-full w-full">
        <toc-content
            v-if="content"
            ref="content"
            catalogue
            :external-content="content"
            expression-id=""
            no-actions
            @references="handleContentReferences"
            volume="1"
            last-volume="1"
        />
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.ref-item {
  border-bottom: 1px solid #ececec;
  padding: 0.5rem 1rem;
  cursor: pointer;
}

.work-id-display {
  margin-left: 1rem;
  background-color: #ecf6f8;
  border: 1px solid #daeef1;
  height: 32px;
  padding: 0 10px;
  line-height: 30px;
  font-size: 12px;
  color: #64B775;
  border-radius: 4px;
  box-sizing: border-box;
  white-space: nowrap;
}
</style>
