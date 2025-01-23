<template>
  <div class="period-select-enhanced">
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>LE-Periode</label>
          <enhanced-select
            v-model="selectedPeriod"
            :options="periods"
            placeholder="LE-Periode auswählen"
            @change="onPeriodChange"
            :disabled="disabled"
          />
        </div>
      </div>

      <div class="col-md-6" v-if="selectedPeriod?.categories?.length > 0">
        <div class="form-group">
          <label>LE Kategorie</label>
          <enhanced-select
            v-model="selectedCategory"
            :options="selectedCategories"
            placeholder="Kategorie auswählen"
            @change="onCategoryChange"
            :disabled="disabled"
          />
        </div>
      </div>
    </div>

    <div class="row" v-if="selectedCategory?.articles?.length > 0">
      <div class="col-md-6">
        <div class="form-group">
          <label>LE Artikel</label>
          <enhanced-select
            v-model="selectedArticle"
            :options="selectedArticles"
            placeholder="Artikel auswählen"
            @change="onArticleChange"
            :disabled="disabled"
          />
        </div>
      </div>

      <div class="col-md-6" v-if="selectedArticle?.methods?.length > 0">
        <div class="form-group">
          <label>LE Handlungsmethode</label>
          <enhanced-select
            v-model="selectedMethod"
            :options="selectedMethods"
            placeholder="Methode auswählen"
            @change="onMethodChange"
            :disabled="disabled"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import EnhancedSelect from "./EnhancedSelect.vue";

export default {
  components: {
    EnhancedSelect
  },
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
    disabled: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      periods: [],
      selectedPeriod: null,
      selectedCategory: null,
      selectedArticle: null,
      selectedMethod: null,
      selectedCategories: [],
      selectedArticles: [],
      selectedMethods: []
    };
  },
  watch: {
    lePeriod: {
      immediate: true,
      handler(newVal) {
        console.log('lePeriod changed:', newVal);
        if (this.periods.length > 0) {
          const periodId = newVal?.id || newVal;
          const period = this.periods.find(p => p.id === periodId);
          console.log('Found period:', period);
          if (period) {
            this.selectedPeriod = period;
            this.selectedCategories = period.categories || [];
          } else {
            this.selectedPeriod = null;
            this.selectedCategories = [];
          }
        }
      }
    },
    leFundingCategory: {
      immediate: true,
      handler(newVal) {
        console.log('leFundingCategory changed:', newVal);
        if (this.selectedCategories.length > 0) {
          const categoryId = newVal?.id || newVal;
          const category = this.selectedCategories.find(c => c.id === categoryId);
          console.log('Found category:', category);
          if (category) {
            this.selectedCategory = category;
            this.selectedArticles = category.articles || [];
          } else {
            this.selectedCategory = null;
            this.selectedArticles = [];
          }
        }
      }
    },
    leFundingArticle: {
      immediate: true,
      handler(newVal) {
        if (this.selectedArticles.length > 0) {
          const articleId = newVal?.id || newVal;
          const article = this.selectedArticles.find(a => a.id === articleId);
          if (article) {
            this.selectedArticle = article;
            this.selectedMethods = article.methods || [];
          } else {
            this.selectedArticle = null;
            this.selectedMethods = [];
          }
        }
      }
    },
    leFundingMethod: {
      immediate: true,
      handler(newVal) {
        if (this.selectedMethods.length > 0) {
          const methodId = newVal?.id || newVal;
          const method = this.selectedMethods.find(m => m.id === methodId);
          if (method) {
            this.selectedMethod = method;
          } else {
            this.selectedMethod = null;
          }
        }
      }
    },
    periods: {
      immediate: true,
      handler(newPeriods) {
        console.log('Periods loaded:', newPeriods);
        if (newPeriods.length > 0 && this.lePeriod) {
          const periodId = this.lePeriod?.id || this.lePeriod;
          const period = newPeriods.find(p => p.id === periodId);
          console.log('Found period after load:', period);
          if (period) {
            this.selectedPeriod = period;
            this.selectedCategories = period.categories || [];

            if (this.leFundingCategory) {
              const categoryId = this.leFundingCategory?.id || this.leFundingCategory;
              const category = this.selectedCategories.find(c => c.id === categoryId);
              console.log('Found category after load:', category);
              if (category) {
                this.selectedCategory = category;
                this.selectedArticles = category.articles || [];

                if (this.leFundingArticle) {
                  const articleId = this.leFundingArticle?.id || this.leFundingArticle;
                  const article = this.selectedArticles.find(a => a.id === articleId);
                  console.log('Found article after load:', article);
                  if (article) {
                    this.selectedArticle = article;
                    this.selectedMethods = article.methods || [];

                    if (this.leFundingMethod) {
                      const methodId = this.leFundingMethod?.id || this.leFundingMethod;
                      const method = this.selectedMethods.find(m => m.id === methodId);
                      console.log('Found method after load:', method);
                      if (method) {
                        this.selectedMethod = method;
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  },
  methods: {
    onPeriodChange(period) {
      this.selectedPeriod = period;
      this.selectedCategories = period ? period.categories || [] : [];
      this.selectedCategory = null;
      this.selectedArticles = [];
      this.selectedArticle = null;
      this.selectedMethods = [];
      this.selectedMethod = null;
      this.emitChange();
    },
    onCategoryChange(category) {
      this.selectedCategory = category;
      this.selectedArticles = category ? category.articles || [] : [];
      this.selectedArticle = null;
      this.selectedMethods = [];
      this.selectedMethod = null;
      this.emitChange();
    },
    onArticleChange(article) {
      this.selectedArticle = article;
      this.selectedMethods = article ? article.methods || [] : [];
      this.selectedMethod = null;
      this.emitChange();
    },
    onMethodChange(method) {
      this.selectedMethod = method;
      this.emitChange();
    },
    emitChange() {
      this.$emit('update', {
        lePeriod: this.selectedPeriod?.id || null,
        leFundingCategory: this.selectedCategory?.id || null,
        leFundingArticle: this.selectedArticle?.id || null,
        leFundingMethod: this.selectedMethod?.id || null
      });
    },
    async fetchPeriods() {
      try {
        const response = await axios.get("/api/v1/le-structure");
        this.periods = response.data;
      } catch (error) {
        console.error('Error fetching LE structure:', error);
      }
    }
  },
  async mounted() {
    console.log('PeriodSelectEnhanced mounted with props:', {
      lePeriod: this.lePeriod,
      leFundingCategory: this.leFundingCategory
    });
    await this.fetchPeriods();
    console.log('After fetch, periods:', this.periods);
    console.log('Selected values:', {
      selectedPeriod: this.selectedPeriod,
      selectedCategory: this.selectedCategory
    });
  }
};
</script>

<style lang="scss" scoped>
.period-select-enhanced {

  .form-group {
    margin-bottom: 0;
  }

  label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #495057;
  }
}
</style> 