<script>
export default {
  name: 'NormaIcon',
  props: {
    name: {
      type: String,
      required: true,
    },
    fixedWidth: {
      type: Boolean,
      default: false,
    },
    iconSize: {
      type: String,
      default: 'lg',
    },
    iconStyle: {
      type: Object,
      default: () => {},
    },
    marginRight: {
      type: String,
      default: '',
    },
    type: {
      type: String,
      default: 'fal',
    },
  },
  computed: {
    theStyle() {
      const sty = this.iconStyle || {};
      sty.marginRight = this.marginRight;
      return sty;
    },
    cssClass() {
      return this.getClass();
    },
  },
  methods: {
    getClass() {
      const css = {};
      css['norma-icon'] = true;
      css[this.type] = true;
      css[`fa-${this.name}`] = true;
      css['fa-fw'] = this.fixedWidth;
      css[`fa-${this.iconSize}`] = true;
      return css;
    },
  },
};
</script>

<template>
  <i :class="cssClass" :style="theStyle" :name="name" />
</template>
