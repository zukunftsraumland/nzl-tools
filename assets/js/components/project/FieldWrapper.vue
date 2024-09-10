<template>
  <div class="project-component-form-row">
    <div class="project-component-form-section">
      <div class="row">
        <div v-for="field in fields" :class="`col-md-${field.columnsize ? field.columnsize : 12}`" :key="field.name">
          <label :for="field.name" class="field-label">
            {{ field.label }}
            <!-- Tooltip Icon -->
            <span v-if="field.tooltip" class="tooltip-icon">
              <span class="question-mark-icon">?</span>
              <span class="tooltip-text">{{ field.tooltip }}</span>
            </span>
          </label>
          <component v-if="isSupportetFieldType(field.type)" v-model="localProject[field.name]"
            :is="getInputType(field.type)" :id="field.name" :value="localProject[field.name]"
            :class="getClassesByType(field.type)" :editor="field.type === 'ckeditor' ? field.editor : null"
            :config="field.type === 'ckeditor' ? field.editorConfig : null" :type="field.type"
            @input="updateField(field.name, $event)" />
          <select v-if="field.type === 'select'" v-model="localProject[field.name]" :value="localProject[field.name]"
            :id="field.name" @input="updateField(field.name, $event)" class="form-control">
            <option v-for="option in field.options" :key="option.id" :value="option.id">
              {{ option.name }}
            </option>
          </select>
          <div v-if="field.type === 'checkbox'" class="toggle-container">
            <input type="checkbox" :id="field.name" v-model="localProject[field.name]"
              @change="updateField(field.name, $event)" class="toggle-input" />
            <label :for="field.name" class="toggle-label"></label>
          </div>
          <div v-if="field.type === 'tag-select'">
            <tag-selector :id="field.name" :model="localProject[field.name]" :options="field.options"
              :searchType="'select'" :labelSelectAll="field.selectAllValue"></tag-selector>
          </div>
          <div v-if="field.type === 'tag-search-select'">
            <!-- MAP_TODO: Tag Selector has to be disabled with class because TagSelector does not allow it -->
            <tag-search-select :id="`${field.name}`" :model="localProject[field.name]" :options="field.options"
              :context="field.context"></tag-search-select>
          </div>
          <!-- Period Select Custom Component -->
          <div>
            <period-select v-if="field.type === 'period-select'" :lePeriod="localProject.lePeriod"
              :leFundingCategory="localProject.leFundingCategory" :leFundingArticle="localProject.leFundingArticle"
              :leFundingMethod="localProject.leFundingMethod" @update="updateField(field.name, $event)" />
          </div>
        </div>
      </div>
    </div>

    <!-- Diff Section -->
    <div class="project-component-form-section" v-if="diff">
      <div class="row">
        <div v-for="field in fields" :class="`col-md-${field.columnsize ? field.columnsize : 12
          } ${getDisabledClassesByType(
            field.type,
            localProject[field.name],
            diff[field.name]
          )}`" :key="field.name + '-diff'">
          <label :for="`${field.name}Diff`">
            <span class="material-icons" v-if="localProject[field.name] !== diff[field.name]"
              @click="mergeFields(field.name)">keyboard_backspace</span>
            <span class="material-icons" v-if="field.name === 'fundingStructure'"
              @click="mergeFields(field.name)">keyboard_backspace</span>
            {{ field.label }}
          </label>
          <component v-if="isSupportetFieldType(field.type)" v-model="diff[field.name]" :is="getInputType(field.type)"
            :id="`${field.name}Diff`" :value="diff[field.name]" :options="field.options"
            :class="getClassesByType(field.type)" :type="field.type"
            :editor="field.type === 'ckeditor' ? field.editor : null"
            :config="field.type === 'ckeditor' ? field.editorConfig : null" readonly />
          <select v-if="field.type === 'select'" :id="`${field.name}Diff`" v-model="diff[field.name]"
            class="form-control" disabled>
            <option v-for="option in field.options" :key="option.id" :value="option.id">
              {{ option.name }}
            </option>
          </select>
          <div v-if="field.type === 'checkbox'" class="toggle-container">
            <input type="checkbox" :id="`${field.name}Diff`" v-model="diff[field.name]" class="toggle-input" />
            <label :for="field.name" class="toggle-label"></label>
          </div>
          <div v-if="field.type === 'tag-select'">
            <!-- MAP_TODO: Tag Selector has to be disabled with class because TagSelector does not allow it -->
            <tag-selector :id="`${field.name}Diff`" :model="diff[field.name]" :options="field.options"
              :searchType="'select'" :labelSelectAll="field.selectAllValue"></tag-selector>
          </div>
          <div v-if="field.type === 'tag-search-select'">
            <tag-search-select :id="`${field.name}Diff`" :model="diff[field.name]" :options="field.options"
              :context="field.context"></tag-search-select>
          </div>
          <!-- Period Select Custom Component -->

          <div>
            <period-select v-if="field.type === 'period-select'" :lePeriod="diff.lePeriod"
              :leFundingCategory="diff.leFundingCategory" :leFundingArticle="diff.leFundingArticle"
              :leFundingMethod="diff.leFundingMethod" @update="updateField" />
          </div>
        </div>
      </div>
    </div>
    <div class="project-component-form-section" v-else></div>
  </div>
</template>

<script>
import TagSelector from "../TagSelector.vue";
import TagSearchSelect from "../TagSearchSelect.vue";
import PeriodSelect from '../PeriodSelect.vue';

export default {
  emits: ['update:project', 'mergeFields'],
  props: {
    fields: Array,
    project: Object,
    diff: Object,
    locale: String,
  },
  components: {
    TagSelector,
    TagSearchSelect,
    PeriodSelect,
  },
  data() {
    return {
      localProject: { ...this.project },
    };
  },
  watch: {
    project: {
      handler(newProject) {
        this.localProject = { ...newProject };
        if (this.localProject.localWorkgroup?.id !== undefined) {
          this.localProject.localWorkgroup = this.localProject.localWorkgroup.id;
        }
      },
      deep: true,
    },
  },
  methods: {
    updateField(fieldName, event) {
      if (fieldName === 'fundingStructure') {
        this.localProject.lePeriod = event.lePeriod;
        this.localProject.leFundingCategory = event.leFundingCategory;
        this.localProject.leFundingArticle = event.leFundingArticle;
        this.localProject.leFundingMethod = event.leFundingMethod;
      } else {

        let value = event.target ? event.target.value : event;

        if (event.target) {
          const isCheckbox = event.target.type === "checkbox";
          value = isCheckbox ? event.target.checked : event.target.value;
        }
        this.localProject[fieldName] = value;
      }

      this.$emit("update:project", { ...this.localProject });
    },
    mergeFields(field) {
      this.$emit("mergeFields", field);
    },
    getInputType(type) {
      switch (type) {
        case "text":
          return "input";
        case "ckeditor":
          return "ckeditor";
        default:
          return "input";
      }
    },
    isSupportetFieldType(type) {
      return type === "text" || type === "ckeditor";
    },
    getComponentTypeProps(type) {
      return type !== "select" ? { type } : {};
    },
    getClassesByType(type) {
      return type === "checkbox" || type === "ckeditor"
        ? "placeholderclass"
        : "form-control";
    },
    getDisabledClassesByType(type, currVal, diffVal) {
      if (currVal !== undefined && type !== "ckeditor" && currVal === diffVal) {
        return " disabled ";
      }

      if (type === "ckeditor" && currVal !== undefined && diffVal !== undefined) {
        return this.compareHTML(currVal, diffVal) ? " disabled " : "";
      }
      return "";
    },
    compareHTML(a, b) {
      return (
        a
          .replace(/(<([^>]+)>)/gi, "")
          .replace(/&nbsp;/gi, " ")
          .replace(/\s+/g, "") ===
        b
          .replace(/(<([^>]+)>)/gi, "")
          .replace(/&nbsp;/gi, " ")
          .replace(/\s+/g, "")
      );
    },
  },
};
</script>

<style scoped>
.tooltip-icon {
  margin-left: 8px;
  position: relative;
  display: inline-block;
}

.question-mark-icon {
  display: inline-block;
  width: 20px;
  height: 20px;
  background-color: #5077b2;
  color: white;
  text-align: center;
  border-radius: 50%;
  font-weight: bold;
  line-height: 20px;
  font-size: 14px;
  cursor: pointer;
}

.tooltip-icon .tooltip-text {
  visibility: hidden;
  width: 350px;
  background-color: #fff;
  color: #333;
  text-align: justify;
  font-size: 0.8rem;
  border-radius: 6px;
  padding: 8px;
  position: absolute;
  z-index: 2;
  bottom: 125%;
  left: 50%;
  margin-left: -75px;
  opacity: 0;
  transition: opacity 0.3s;
  border-width: 1px;
  border-style: solid;
  border-color: #333;
}

.tooltip-icon:hover .tooltip-text {
  visibility: visible;
  opacity: 1;
}

.tooltip-icon:hover .tooltip-text {
  bottom: auto;
  top: 125%;
}

.tooltip-icon:hover .tooltip-text::after {
  top: auto;
  bottom: 100%;
  border-color: transparent transparent #333 transparent;
}

.toggle-container {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 34px;
}

.toggle-input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-label {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  border-radius: 25px;
  cursor: pointer;
  transition: background-color 0.2s;
}

.toggle-label::before {
  content: "";
  position: absolute;
  height: 21px;
  width: 21px;
  left: 2px;
  bottom: 2px;
  background-color: white;
  border-radius: 50%;
  transition: transform 0.2s;
}

.toggle-input:checked+.toggle-label {
  background-color: #5077b2;
}

.toggle-input:checked+.toggle-label::before {
  transform: translateX(24px);
}
</style>
