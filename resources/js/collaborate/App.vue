<template>
  <ToCPage />
  <ConfirmAction />
</template>


<script>
import {defineComponent} from "vue";
import ToCPage from "./TocPage.vue";
import ConfirmAction from "./components/ConfirmAction.vue";

export default defineComponent({
  name: 'App',
  components: {ConfirmAction, ToCPage},
});
</script>
