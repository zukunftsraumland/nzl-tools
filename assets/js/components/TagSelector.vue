<template>
  <div class="tag-selector-component">
    <div class="tag-selector-component-selection">
      <template v-if="isOptGroup">
        <div
          class="tag-selector-component-selection-tag"
          v-for="option in groupOptions"
          @click="removeOption(option)"
          v-if="showSelection"
        >
          <span v-if="option && option[label]">{{ option[label] }}</span>
        </div>
      </template>

      <template v-else>
        <div
          class="tag-selector-component-selection-tag"
          v-for="option in model.map((option) => options.find((o) => o.id === option.id))"
          @click="removeOption(option)"
          v-if="showSelection"
        >
          <span v-if="option && option[label]">{{ option[label] }}</span>
        </div>
      </template>

      <template v-if="!readonly">
        <input
          class="tag-selector-component-selection-search"
          type="text"
          v-model="term"
          v-if="searchType === 'text'"
        />

        <enhanced-select
          v-if="searchType === 'select'"
          :options="filterOptions(options)"
          :placeholder="fieldLabel ? `${fieldLabel} auswählen` : 'Bitte auswählen'"
          @change="selectOption"
          :show-placeholder="true"
          :alwaysShowPlaceholder="true"
        />
      </template>
    </div>

    <div
      class="tag-selector-component-options"
      v-if="searchType === 'text' && filterOptions(options, term).length"
    >
      <div
        class="tag-selector-component-options-option"
        v-for="option in filterOptions(options, term)"
        @click="selectOption(option)"
      >
        {{ option[label] }}
      </div>
    </div>
  </div>
</template>

<script>
import EnhancedSelect from './EnhancedSelect.vue';

export default {
  components: {
    EnhancedSelect
  },
  emits: ["change"],
  props: {
    model: Array,
    options: Array,
    label: {
      type: String,
      default: "name",
    },
    readonly: Boolean,
    searchType: {
      type: String,
      default: "text",
    },
    showSelection: {
      type: Boolean,
      default: true,
    },
    isOptGroup: {
      type: Boolean,
      default: false,
    },
    enableSelectAll: {
      type: Boolean,
      default: false,
    },
    fieldLabel: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      term: "",
    };
  },
  methods: {
    selectOption(option) {
      if (this.readonly) return false;
      if (option && option.__selectAll) {
        this.toggleSelectAll();
        return;
      }
      if (this.getOptionById(option.id)) return false;
      this.term = "";
      this.model.push(option);
      this.$emit("change", this.model);
    },
    removeOption(option) {
      if (this.readonly) return false;
      let remove = this.getOptionById(option.id);
      if (remove) {
        this.model.splice(this.model.indexOf(remove), 1);
        this.$emit("change", this.model);
      }
    },
    getOptionById(id) {
      return this.model.find((option) => option.id === id);
    },
    filterOptions(options, term = "") {
      if (!options) return [];
      if (this.searchType === "select") {
        let filtered = options
          .filter((option) => !this.getOptionById(option.id))
          .sort((a, b) => a[this.label].localeCompare(b[this.label]));
        if (this.enableSelectAll) {
          const allSelected = this.model.length === this.options.length && this.options.length > 0;
          filtered = [
            {
              __selectAll: true,
              [this.label]: allSelected ? 'Alle abwählen' : 'Alle auswählen',
            },
            ...filtered
          ];
        }
        return filtered;
      }
      return options.filter((option) =>
        option[this.label]?.toLowerCase().includes(term.trim().toLowerCase())
      );
    },
    toggleSelectAll() {
      if (this.model.length === this.options.length && this.options.length > 0) {
        this.model.splice(0);
      } else {
        this.model.splice(0, this.model.length, ...this.options);
      }
      this.$emit("change", this.model);
    },
  },
  computed: {
    isAllSelected() {
      return this.model.length === this.options.length;
    },
    groupOptions() {
      if (!this.isOptGroup) return [];
      return this.model.map((option) => {
        for (let group of this.options) {
          let found = group.children.find((o) => o.id === option.id);
          if (found) return found;
        }
        return null;
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.tag-selector-component {
  &-selection {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 4px;
    min-height: 38px;
    border-radius: 4px;
    background: white;

    &-tag {
      display: inline-flex;
      align-items: center;
      padding: 4px 8px;
      background: #5077b2;
      color: white;
      border-radius: 4px;
      cursor: pointer;

      &:hover {
        background: #C00;
      }
    }

    &-search {
      flex: 1;
      min-width: 100px;
      border: none;
      outline: none;
      font-size: 14px;
      
      &:focus {
        outline: none;
      }
    }
  }

  &-options {
    position: absolute;
    z-index: 1000;
    width: 100%;
    margin-top: 4px;
    background: white;
    border: 1px solid #5077b2;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);

    &-option {
      padding: 8px 12px;
      cursor: pointer;

      &:hover {
        background: #f8f9fa;
      }
    }
  }
}
</style>
