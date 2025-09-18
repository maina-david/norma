<script>
import TocReferenceTypeSelector from './TocReferenceTypeSelector.vue';

export default {
  name: 'TocReferenceButtons',
  components: { TocReferenceTypeSelector },
  props: {
    isSection: { type: Boolean, required: true },
    type: { type: String, default: '' },
  },
  data() {
    return {
      types: [
        'Article',
        'Chapter',
        'Division',
        'Part',
        'Section',
        'Title',
      ],
    };
  },
};
</script>

<template>
  <div class="btn-group" role="group">
    <button
      data-toggle="tooltip"
      title="Is a section"
      type="button"
      :class="{'bg-primary border border-primary text-white': isSection, 'btn-outline-secondary': !isSection }"
      class="btn "
      @click.stop="$emit('section', !isSection)"
    >
      §
    </button>
    <button
      data-toggle="tooltip"
      title="Increase Level"
      type="button"
      class="btn btn-outline-secondary btn-sm"
      @click.stop="$emit('indent')"
    >
      <span>
        <norma-icon name="indent" />
      </span>
    </button>
    <button
      data-toggle="tooltip"
      title="Reduce Level"
      type="button"
      class="btn btn-outline-secondary btn-sm"
      @click.stop="$emit('outdent')"
    >
      <span>
        <norma-icon name="outdent" />
      </span>
    </button>

    <toc-reference-type-selector @change="(e) => $emit('type', e)" />
  </div>
</template>

<style lang="scss">
.drop-holder {
  position: relative;

  &:hover .dropdown-menu {
    display: block;
  }
}
</style>
