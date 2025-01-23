<template>
  <enhanced-select
    v-model="selectValue"
    :options="options"
    :placeholder="'Bitte wählen'"
    @change="onOptionChange"
    :class="{
      success: selectValue && !visibleOptions,
      warning: selectValue && selectValue.isPublic === false && !visibleOptions
    }"
  />
</template>

<script>
import EnhancedSelect from './EnhancedSelect.vue';

export default {
  components: {
    EnhancedSelect
  },
  props: {
    'value': Object,
    'options': Array,
    'label': {
      type: String,
      default: 'name'
    },
  },
  data() {
    return {
      selectValue: null,
      visibleOptions: false,
    };
  },
  watch: {
    value(curVal, oldVal) {
      if(curVal && curVal !== oldVal) {
        this.selectValue = curVal;
      }
    }
  },
  methods: {
    onOptionChange(option) {
      this.selectValue = option || {};
      this.$emit('update', this.selectValue);
    }
  },
  created() {
    if(this.value) {
      this.selectValue = this.value;
    }
  }
}
</script>

<style lang="scss" scoped>
.success {
  border-color: #28a745 !important;
  &:hover {
    border-color: #218838 !important;
  }
}

.warning {
  border-color: #ffc107 !important;
  &:hover {
    border-color: #e0a800 !important;
  }
}
</style>