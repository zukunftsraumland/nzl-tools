<template>
  <div class="project-component-form-row">
    <div class="project-component-form-section">
      <div class="row">
        <div
          v-for="field in fields"
          :class="`col-md-${field.columnsize ? field.columnsize : 12}`"
          :key="field.name"
        >
          <label :for="field.name" class="field-label">
            <div @click="showInputField(field.name)">
              {{ field.label }}
              <span
                v-if="field.isAccordion && !localProject[field.name]"
                class="chevron-down-icon"
              >
                {{ openedFields.includes(field.name) ? "▼" : "▶" }}
              </span>
            </div>
            <span v-if="field.tooltip" class="tooltip-icon">
              <span @click="toggleTooltip(field.name)" class="question-mark-icon">?</span>
            </span>
          </label>
          <p
            :class="{
              'tooltip-text': true,
              'tooltip-text-visible': visibleTooltips.includes(field.name),
            }"
          >
            {{ field.tooltip }}
          </p>
          <div
            v-show="
              openedFields.includes(field.name) ||
              localProject[field.name] ||
              (diff != null && diff[field.name]) ||
              !field.isAccordion
            "
          >
            <component
              v-if="isSupportetFieldType(field.type)"
              v-model="localProject[field.name]"
              :is="getInputType(field.type)"
              :id="field.name"
              :value="localProject[field.name]"
              :class="getClassesByType(field.type)"
              :editor="field.type === 'ckeditor' ? field.editor : null"
              :config="field.type === 'ckeditor' ? field.editorConfig : null"
              :type="field.type"
              @input="updateField(field.name, $event)"
            />
            <select
              v-if="field.type === 'select'"
              v-model="localProject[field.name]"
              :value="localProject[field.name]"
              :id="field.name"
              @input="updateField(field.name, $event)"
              class="form-control"
            >
              <option v-for="option in field.options" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
            <div v-if="field.type === 'checkbox'" class="toggle-container">
              <input
                type="checkbox"
                :id="field.name"
                v-model="localProject[field.name]"
                @change="updateField(field.name, $event)"
                class="toggle-input"
              />
              <label :for="field.name" class="toggle-label"></label>
            </div>
            <div v-if="field.type === 'tag-select'">
              <tag-selector
                :id="field.name"
                :model="localProject[field.name]"
                :options="field.options"
                :searchType="'select'"
                :labelSelectAll="field.selectAllValue"
              ></tag-selector>
            </div>
            <div v-if="field.type === 'tag-search-select'">
              <!-- MAP_TODO: Tag Selector has to be disabled with class because TagSelector does not allow it -->
              <tag-search-select
                :id="`${field.name}`"
                :model="localProject[field.name]"
                :options="field.options"
                :context="field.context"
              ></tag-search-select>
            </div>
            <!-- Period Select Custom Component -->
            <div>
              <period-select
                v-if="field.type === 'period-select'"
                :lePeriod="localProject.lePeriod"
                :leFundingCategory="localProject.leFundingCategory"
                :leFundingArticle="localProject.leFundingArticle"
                :leFundingMethod="localProject.leFundingMethod"
                @update="updateField(field.name, $event)"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Diff Section -->
    <div class="project-component-form-section" v-if="diff">
      <div class="row">
        <div
          v-for="field in fields"
          :class="`col-md-${
            field.columnsize ? field.columnsize : 12
          } ${getDisabledClassesByType(
            field.type,
            localProject[field.name],
            diff[field.name]
          )}`"
          :key="field.name + '-diff'"
        >
          <label :for="`${field.name}Diff`">
            <span
              class="material-icons"
              v-if="localProject[field.name] !== diff[field.name]"
              @click="mergeFields(field.name)"
              >keyboard_backspace</span
            >
            <span
              class="material-icons"
              v-if="field.name === 'fundingStructure'"
              @click="mergeFields(field.name)"
              >keyboard_backspace</span
            >
            {{ field.label }}
          </label>
          <div
            v-show="
              openedFields.includes(field.name) ||
              localProject[field.name] ||
              (diff != null && diff[field.name]) ||
              !field.isAccordion
            "
          >
            <component
              v-if="isSupportetFieldType(field.type)"
              v-model="diff[field.name]"
              :is="getInputType(field.type)"
              :id="`${field.name}Diff`"
              :value="diff[field.name]"
              :options="field.options"
              :class="getClassesByType(field.type)"
              :type="field.type"
              :editor="field.type === 'ckeditor' ? field.editor : null"
              :config="field.type === 'ckeditor' ? field.editorConfig : null"
              readonly
            />
            <select
              v-if="field.type === 'select'"
              :id="`${field.name}Diff`"
              v-model="diff[field.name]"
              class="form-control"
              disabled
            >
              <option v-for="option in field.options" :key="option.id" :value="option.id">
                {{ option.name }}
              </option>
            </select>
            <div v-if="field.type === 'checkbox'" class="toggle-container">
              <input
                type="checkbox"
                :id="`${field.name}Diff`"
                v-model="diff[field.name]"
                class="toggle-input"
              />
              <label :for="field.name" class="toggle-label"></label>
            </div>
            <div v-if="field.type === 'tag-select'">
              <!-- MAP_TODO: Tag Selector has to be disabled with class because TagSelector does not allow it -->
              <tag-selector
                :id="`${field.name}Diff`"
                :model="diff[field.name]"
                :options="field.options"
                :searchType="'select'"
                :labelSelectAll="field.selectAllValue"
              ></tag-selector>
            </div>
            <div v-if="field.type === 'tag-search-select'">
              <tag-search-select
                :id="`${field.name}Diff`"
                :model="diff[field.name]"
                :options="field.options"
                :context="field.context"
              ></tag-search-select>
            </div>
            <!-- Period Select Custom Component -->

            <div>
              <period-select
                v-if="field.type === 'period-select'"
                :lePeriod="diff.lePeriod"
                :leFundingCategory="diff.leFundingCategory"
                :leFundingArticle="diff.leFundingArticle"
                :leFundingMethod="diff.leFundingMethod"
                :project="localProject"
                @update="updateField"
              />
            </div>
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
import PeriodSelect from "../PeriodSelect.vue";

export default {
  emits: ["update:project", "mergeFields"],
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
      visibleTooltips: [],
      openedFields: [], // Track which accordion fields are open
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
      if (fieldName === "fundingStructure") {
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
    showInputField(fieldName) {
      if (this.openedFields.includes(fieldName)) {
        this.openedFields = this.openedFields.filter((name) => name !== fieldName);
      } else {
        this.openedFields.push(fieldName);
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
      switch (type) {
        case "ckeditor":
          return this.compareHTML(currVal, diffVal) ? " disabled " : "";
        case "tag-search-select":
        case "tag-select":
          return this.compareTags(currVal, diffVal) ? " disabled " : "";
        case "period-select":
          return this.compareLEPeriod(currVal, diffVal) ? " disabled " : "";
        default:
          return currVal === diffVal ? " disabled " : "";
      }
    },

    toggleTooltip(fieldName) {
      if (this.visibleTooltips.includes(fieldName)) {
        this.visibleTooltips = this.visibleTooltips.filter((name) => name !== fieldName);
      } else {
        this.visibleTooltips.push(fieldName);
      }
    },
    compareTags(currVal, diffVal) {
      if (!Array.isArray(currVal) || !Array.isArray(diffVal)) {
        return currVal === diffVal;
      }
      if (currVal.length !== diffVal.length) {
        return false;
      }

      return currVal.every((tag, index) => {
        return tag.id === diffVal[index].id; // Assuming each tag has an `id` property
      });
    },
    compareLEPeriod(currVal, diffVal) {
      if (!currVal || !diffVal) return false;

      return (
        currVal.lePeriod === diffVal.lePeriod &&
        currVal.leFundingCategory === diffVal.leFundingCategory &&
        currVal.leFundingArticle === diffVal.leFundingArticle &&
        currVal.leFundingMethod === diffVal.leFundingMethod
      );
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

.toggle-input:checked + .toggle-label {
  background-color: #5077b2;
}

.toggle-input:checked + .toggle-label::before {
  transform: translateX(24px);
}

.tooltip-text {
  overflow: hidden;
  margin: 0;
  max-height: 0;
  opacity: 0;
  transition: max-height 0.3s ease, opacity 0.3s ease;
  font-size: 0.7rem;
}

.tooltip-text-visible {
  color: #333;
  margin-top: 0.5rem;
  margin-bottom: 0.5rem;
  max-height: 100px;
  opacity: 1;
}

.field-label {
  display: flex;
  justify-content: space-between;
}

.chevron-down-icon {
  cursor: pointer;
  font-size: 0.5rem;
  padding-left: 8px;
}
</style>
