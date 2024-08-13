<script>
import TocReferenceButtons from './TocReferenceButtons.vue';

export default {
  name: 'ToCReference',
  components: {
    TocReferenceButtons,
  },
  props: {
    reference: { type: Object, required: true },
    checked: { type: Boolean, required: true },
    noActions: { type: Boolean, default: false },
    noDelete: { type: Boolean, default: false },
  },
  computed: {
    isNew() {
      return this.reference.id && this.reference.id.toString().indexOf('li') === 0;
    },
    label() {
      if (this.reference.ref_plain_text && this.reference.ref_plain_text.plain_text.trim().length > 0) {
        return this.reference.ref_plain_text.plain_text;
      }

      if (this.reference.ref_selector && this.reference.ref_selector.selectors) {
        const itemQuote = this.reference.ref_selector.selectors.filter((sel) => sel.type === 'TextQuoteSelector')[0];

        return itemQuote ? itemQuote.exact : '';
      }

      return this.reference.text;
    },
    canDelete() {
      return document.querySelector('#app').getAttribute('data-delete') == 1;
    },
    showDelete() {
      return !this.noDelete && this.canDelete
        && !Number.isNaN(parseInt(this.reference.id, 10))
        && this.reference.created_at && this.reference.updated_at;
    },
  },
  methods: {
    handleChecked() {
      this.$emit('check');
    },
    handleIsSection() {
      this.$emit('section', !this.reference.is_section);
    },
    handleDelete() {
      this.$confirm({ title: 'Delete Reference?', message: 'Are you sure you want to delete this reference?', confirm: 'Delete' })
        .then(() => {
          this.$emit('delete', this.reference);
        });
    },
  },
};
</script>

<template>
  <div class="ref flex items-center pl-4 py-1 border-b border-gray-200"
    :class="{ 'new': isNew, orphaned: !!reference.orphaned, 'pending': reference.status == 1 }">
    <input v-if="!noActions" class="mr-2 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded flex-shrink-0"
      type="checkbox" :checked="checked" @click.stop="handleChecked">
    <div v-if="!noActions" class="actions">
      <toc-reference-buttons :is-section="!!reference.is_section" @section="handleIsSection"
        @indent="() => $emit('indent')" @outdent="() => $emit('outdent')" @type="(e) => $emit('type', e)" />
    </div>
    <div :style="`margin-left:${reference.level}rem;border-left: 1px dotted;padding-left:0.5rem;cursor:pointer;`"
      class="overflow-hidden w-full flex-grow flex flex-col justify-center" data-toggle="tooltip" data-delay="500"
      :title="reference.ref_plain_text ? reference.ref_plain_text.plain_text : reference.text"
      @click.stop="$emit('click', reference)">
      <div class="overflow-hidden text-nowrap" style="text-overflow:ellipsis;">
        {{ label }}
      </div>
    </div>
    <div v-if="!noActions && showDelete">
      <button class="btn btn-sm btn-outline-danger" @click.prevent="handleDelete">
        <span>
          <libryo-icon name="trash-alt" icon-size="sm" />
        </span>
      </button>
    </div>
  </div>
</template>

<style scoped>
.ref.new {
  background: #9dd1d6;
}

.ref.orphaned {
  text-decoration: line-through;
  font-weight: 600;
  color: #e05773;
}

.ref.pending {
  position: relative;
}

.ref.pending:before {
  content: '';
  position: absolute;
  height: 100%;
  width: 6px;
  background: #e05773;
  left: 0;
  top: 0;
}
</style>
