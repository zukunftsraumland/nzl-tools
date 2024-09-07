<template>
  <div class="tag-selector-component">
    <!-- Selected Tags -->
    <div class="tag-selector-component-selection">
      <div v-for="tag in groupOptions" :key="tag.id" class="tag-selector-component-selection-tag">
        {{ tag.name || "Unnamed Tag" }}
        <span @click="removeTag(tag)" class="remove-tag">x</span>
      </div>
    </div>

    <!-- Search Input -->
    <input class="tag-selector-component-selection-search" type="text" v-model="searchTerm" @focus="showDropdown"
      @input="filterTags" @blur="hideDropdown" placeholder="Schlagworte suchen oder erstellen..." />

    <!-- Dropdown for available options -->
    <div class="tag-selector-component-options" v-if="isDropdownVisible && filteredOptions.length">
      <div class="tag-selector-component-options-option" v-for="option in filteredOptions" :key="option.id"
        @mousedown="selectTag(option)">
        {{ option.name }}
      </div>
      <div class="tag-selector-component-options-option" v-if="!isTagExists && searchTerm">
        <span @mousedown="createTag" class="create-new-tag">Add "{{ searchTerm }}"</span>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  emits: ["change"],
  props: {
    model: {
      type: Array,
      default: () => [],
    },
    options: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      searchTerm: "",
      filteredOptions: [],
      isDropdownVisible: false,
    };
  },
  mounted() {
    this.filteredOptions = this.options;
  },
  methods: {
    filterTags() {
      if (this.searchTerm.trim()) {
        this.filteredOptions = this.options.filter((option) =>
          option.name.toLowerCase().includes(this.searchTerm.toLowerCase())
        );
      } else {
        this.filteredOptions = this.options;
      }
    },
    selectTag(tag) {
      if (!this.model.some((selectedTag) => selectedTag.id === tag.id)) {
        this.model.push(tag);
        this.$emit("change", this.model);
      }
      this.searchTerm = "";
      this.isDropdownVisible = false;
    },
    removeTag(tag) {
      const index = this.model.findIndex((selectedTag) => selectedTag.id === tag.id);
      if (index !== -1) {
        this.model.splice(index, 1);
        this.$emit("change", [...this.model]);
      }
    },
    createTag() {
      axios
        .post("/api/v1/tags/create", { name: this.searchTerm })
        .then((response) => {
          const newTag = response.data;
          if (newTag && newTag.name) {
            this.model.push(newTag);
            this.options.push(newTag);
            this.$emit("change", [...this.model]);
          }
          this.searchTerm = "";
          this.filteredOptions = this.options;
          this.isDropdownVisible = false;
        })
        .catch((error) => {
          console.error("Error creating tag:", error);
        });
    },
    showDropdown() {
      this.isDropdownVisible = true;
      this.filteredOptions = this.options; // Ensure all options are shown when dropdown is first opened
    },
    hideDropdown() {
      setTimeout(() => {
        this.isDropdownVisible = false;
      }, 100); // Delay to ensure mousedown events complete before hiding
    },
  },
  computed: {
    isTagExists() {
      return this.options.some(
        (option) => option.name.toLowerCase() === this.searchTerm.toLowerCase()
      );
    },
    groupOptions() {
      if (!this.model) return [];
      let groupOptions = [];
      for (let option of this.model) {
        for (let opt of this.options) {
          if (opt.id === option.id) {
            groupOptions.push({
              ...opt,
            });
          }
        }
      }
      return groupOptions;
    },
  },
};
</script>

<style scoped>
.tag-selector-component {
  position: relative;
}

.tag-selector-component-selection {
  margin-bottom: 10px;
}

.tag-selector-component-selection-tag {
  background-color: #5077b2;
  color: white;
  padding: 5px 10px;
  border-radius: 0.25em;
  display: inline-block;
  margin-right: 5px;
}

.remove-tag {
  margin-left: 8px;
  cursor: pointer;
}

.tag-selector-component-selection-search {
  width: 90% !important;
  padding: 5px;
  margin: 10px;
}

.tag-selector-component-options {
  position: absolute;
  left: -1px;
  background-color: white;
  border: 1px solid #5077b2;
  border-radius: 0.25em;
  width: 100%;
  max-height: 200px;
  overflow-y: auto;
  z-index: 1;
}

.tag-selector-component-options-option {
  padding: 10px;
  cursor: pointer;
}

.tag-selector-component-options-option:hover {
  background-color: #5077b2;
}

.create-new-tag {
  padding: 10px;
  font-weight: bold;
  cursor: pointer;
}

.create-new-tag:hover {
  background-color: #5077b2;
}
</style>
