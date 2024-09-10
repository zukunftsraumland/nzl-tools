<template>
  <div class="tag-selector-component">
    <!-- Selected Tags -->
    <div class="tag-selector-component-selection">
      <div @click="removeTag(tag)" v-for="tag in groupOptions" :key="tag.id"
        class="tag-selector-component-selection-tag">
        {{ tag.name || "Unnamed Tag" }}
      </div>
    </div>

    <!-- Search Input -->
    <input class="tag-selector-component-selection-search" type="text" v-model="searchTerm" @focus="showDropdown"
      @input="filterTags" @blur="hideDropdown" placeholder="Schlagworte suchen oder erstellen..." />

    <!-- Dropdown for available options -->
    <div class="tag-selector-component-options" v-if="isDropdownVisible && filteredOptions.length || searchTerm">
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
    context: {
      type: String,
      default: "",
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
    this.filteredOptions = this.options.filter(
      (option) => !this.model.some((selectedTag) => selectedTag.id === option.id)
    );
  },
  methods: {
    filterTags() {
      if (this.searchTerm.trim()) {
        this.filteredOptions = this.options.filter((option) =>
          option.name.toLowerCase().includes(this.searchTerm.toLowerCase()) &&
          !this.model.some((selectedTag) => selectedTag.id === option.id)
        );
      } else {
        this.filteredOptions = this.options.filter(
          (option) => !this.model.some((selectedTag) => selectedTag.id === option.id)
        );
      }
    },
    selectTag(tag) {
      if (!this.model.some((selectedTag) => selectedTag.id === tag.id)) {
        this.model.push(tag);
        this.$emit("change", this.model);
      }
      this.searchTerm = "";
      this.isDropdownVisible = false;
      this.filteredOptions = this.options.filter(
        (option) => !this.model.some((selectedTag) => selectedTag.id === option.id)
      );
    },
    removeTag(tag) {
      const index = this.model.findIndex((selectedTag) => selectedTag.id === tag.id);
      if (index !== -1) {
        this.model.splice(index, 1);
        this.$emit("change", [...this.model]);
      }
      this.filteredOptions = this.options.filter(
        (option) => !this.model.some((selectedTag) => selectedTag.id === option.id)
      );
    },
    createTag() {
      axios
        .post("/api/v1/tags/create", { name: this.searchTerm, context: this.context })
        .then((response) => {
          const newTag = response.data;
          if (newTag && newTag.name) {
            this.model.push(newTag);
            this.options.push(newTag);
            this.$emit("change", [...this.model]);
          }
          this.searchTerm = "";
          this.filteredOptions = this.options.filter(
            (option) => !this.model.some((selectedTag) => selectedTag.id === option.id)
          );
          this.isDropdownVisible = false;
        })
        .catch((error) => {
          console.error("Error creating tag:", error);
        });
    },
    showDropdown() {
      this.isDropdownVisible = true;
      this.filteredOptions = this.options.filter(
        (option) => !this.model.some((selectedTag) => selectedTag.id === option.id)
      );
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
  width: 100%;
  max-width: 500px;
}

.tag-selector-component-selection {
  margin-bottom: 10px;
  display: flex;
  flex-wrap: wrap;
}

.tag-selector-component-selection-tag {
  background-color: #5077b2;
  color: white;
  padding: 5px 10px;
  border-radius: 0.25em;
  display: inline-flex;
  align-items: center;
  margin-right: 5px;
  margin-bottom: 5px;
}

.tag-selector-component-selection-tag:hover {
  background-color: #CC0000;
}

.remove-tag {
  margin-left: 8px;
  cursor: pointer;
}

.tag-selector-component-selection-search {
  width: 98% !important;
  padding: 8px;
  border: 1px solid #5077b2;
  border-radius: 0.25em;
  margin-bottom: 10px;
  box-sizing: border-box;
}

.tag-selector-component-options {
  position: absolute;
  background-color: white;
  border: 1px solid #5077b2;
  border-radius: 0.25em;
  width: 100%;
  max-height: 200px;
  overflow-y: auto;
  z-index: 1;
  padding: 10px;
  display: flex;
  flex-wrap: wrap;
  box-sizing: border-box;
}

.tag-selector-component-options-option {
  padding: 5px 10px;
  margin: 5px;
  border-radius: 0.25em;
  border: 1px solid #5077b2;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
}

.tag-selector-component-options-option:hover {
  background-color: #5077b2;
  color: white;
}

.create-new-tag {
  padding: 5px 10px;
  margin: 5px;
  font-weight: bold;
  border-radius: 0.25em;
  border: 1px solid #5077b2;
  cursor: pointer;
}

.create-new-tag:hover {
  background-color: #5077b2;
  color: white;
}
</style>
