<template>
  <!-- Period Dropdown -->
  <select v-model="selectedPeriodId" @change="onPeriodChange">
    <option value="">Select LE Period</option>
    <option v-for="period in periods" :key="period.id" :value="period.id">
      {{ period.name }}
    </option>
  </select>

  <!-- Category Dropdown -->
  <select
    v-if="selectedCategories.length > 0 || selectedCategoryId"
    v-model="selectedCategoryId"
    @change="onCategoryChange"
  >
    <option value="">Select Funding Category</option>
    <option
      v-for="category in selectedCategories"
      :key="category.id"
      :value="category.id"
    >
      {{ category.name }}
    </option>
  </select>

  <!-- Article Dropdown -->
  <select
    v-if="selectedArticles.length > 0 || selectedArticleId"
    v-model="selectedArticleId"
    @change="onArticleChange"
  >
    <option value="">Select Funding Article</option>
    <option v-for="article in selectedArticles" :key="article.id" :value="article.id">
      {{ article.name }}
    </option>
  </select>

  <!-- Method Dropdown -->
  <select
    v-if="selectedMethods.length > 0 || selectedMethodId"
    v-model="selectedMethodId"
  >
    <option value="">Select Funding Method</option>
    <option v-for="method in selectedMethods" :key="method.id" :value="method.id">
      {{ method.name }}
    </option>
  </select>
</template>

<script>
import axios from "axios";

export default {
  emits: ["update"],
  props: {
    lePeriod: {
      type: [Object, Number],
      default: null,
    },
    leFundingCategory: {
      type: [Object, Number],
      default: null,
    },
    leFundingArticle: {
      type: [Object, Number],
      default: null,
    },
    leFundingMethod: {
      type: [Object, Number],
      default: null,
    },
  },
  data() {
    return {
      selectedPeriodId: null,
      selectedCategoryId: null,
      selectedArticleId: null,
      selectedMethodId: null,
      periods: [],
      selectedCategories: [],
      selectedArticles: [],
      selectedMethods: [],
    };
  },
  watch: {
    $route(to, from) {
      if (to.params.id !== from.params.id) {
        // Re-fetch the project or reinitialize data when the route changes
        this.fetchPeriods();
      }
    },
    lePeriod: {
      immediate: true,
      handler(newVal) {
        this.selectedPeriodId = newVal?.id || newVal;
      },
    },
    leFundingCategory: {
      immediate: true,
      handler(newVal) {
        this.selectedCategoryId = newVal?.id || newVal;
      },
    },
    leFundingArticle: {
      immediate: true,
      handler(newVal) {
        this.selectedArticleId = newVal?.id || newVal;
      },
    },
    leFundingMethod: {
      immediate: true,
      handler(newVal) {
        this.selectedMethodId = newVal?.id || newVal;
      },
    },
    selectedMethodId() {
      this.emitChange();
    },
    selectedArticleId() {
      this.emitChange();
    },
    selectedCategoryId() {
      this.emitChange();
    },
    selectedPeriodId() {
      this.emitChange();
    },
  },
  methods: {
    fetchPeriods() {
      axios.get("/api/le-structure").then((response) => {
        this.periods = response.data;
        // Automatically select categories, articles, and methods based on the initial IDs
        if (this.selectedPeriodId) {
          const selectedPeriod = this.periods.find((p) => p.id === this.selectedPeriodId);
          if (selectedPeriod) {
            this.selectedCategories = selectedPeriod.categories || [];

            if (this.selectedCategoryId) {
              const selectedCategory = this.selectedCategories.find(
                (c) => c.id === this.selectedCategoryId
              );
              if (selectedCategory) {
                this.selectedArticles = selectedCategory.articles || [];

                if (this.selectedArticleId) {
                  const selectedArticle = this.selectedArticles.find(
                    (a) => a.id === this.selectedArticleId
                  );
                  if (selectedArticle) {
                    this.selectedMethods = selectedArticle.methods || [];
                  }
                }
              }
            }
          }
        }
      });
    },
    onPeriodChange() {
      const selectedPeriod = this.periods.find((p) => p.id === this.selectedPeriodId);
      this.selectedCategories = selectedPeriod?.categories || [];
      this.selectedCategoryId = null;
      this.selectedArticles = [];
      this.selectedMethods = [];
    },
    onCategoryChange() {
      const selectedCategory = this.selectedCategories.find(
        (c) => c.id === this.selectedCategoryId
      );
      this.selectedArticles = selectedCategory?.articles || [];
      this.selectedArticleId = null;
      this.selectedMethods = [];
    },
    onArticleChange() {
      const selectedArticle = this.selectedArticles.find(
        (a) => a.id === this.selectedArticleId
      );
      this.selectedMethods = selectedArticle?.methods || [];
      this.selectedMethodId = null;
    },
    emitChange() {
      this.$emit("update", {
        lePeriod: this.selectedPeriodId,
        leFundingCategory: this.selectedCategoryId,
        leFundingArticle: this.selectedArticleId,
        leFundingMethod: this.selectedMethodId,
      });
      this.fetchPeriods();
    },
  },
  mounted() {
    this.fetchPeriods();
  },
};
</script>

<style scoped>
select {
  display: block;
  margin-bottom: 10px;
  max-width: 500px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  padding: 0.4em 0.25em;
  border-radius: 5px;
  border: 1px solid #5077b2;
  font-size: inherit;
}

select option {
  max-width: 500px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
