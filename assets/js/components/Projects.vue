<template>
  <div class="projects-component">
    <div class="projects-component-title">
      <h2>Projekte</h2>

      <transition name="fade" mode="out-in">
        <div class="loading-indicator" v-if="isLoading('projects')"></div>
      </transition>

      <div class="projects-component-title-actions">
        <a href="/api/v1/projects.xlsx" class="button" download>XLSX</a>
        <router-link :to="'/projects/add'" class="button primary"
          >Neuen Eintrag erstellen</router-link
        >
      </div>
    </div>

    <div class="projects-component-filter">
      <div class="row">
        <div class="col-md-8">
          <div class="form-group">
            <label for="term">Suchbegriff</label>
            <input
              id="term"
              type="text"
              class="form-control"
              v-model="term"
              @change="changeForm()"
            />
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="status">Status</label>
            <div class="select-wrapper">
              <select
                id="status"
                class="form-control"
                @change="
                  addFilter({ type: 'status', value: $event.target.value });
                  $event.target.value = null;
                "
              >
                <option></option>
                <option :value="'public'">Öffentlich</option>
                <option :value="'draft'">Entwurf</option>
              </select>
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="caseStudy">Case Study</label>
            <input
              id="caseStudy"
              type="checkbox"
              v-model="caseStudy"
              @change="changeForm()"
            />
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_START_DATE">
          <div class="form-group">
            <label for="startDate">Start (Jahr)</label>
            <div class="select-wrapper">
              <select
                id="startDate"
                class="form-control"
                @change="
                  addFilter({ type: 'startDate', value: $event.target.value });
                  $event.target.value = null;
                "
              >
                <option></option>
                <option v-for="year in years" :value="year + '-01-01'">
                  {{ year }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_END_DATE">
          <div class="form-group">
            <label for="endDate">Ende (Jahr)</label>
            <div class="select-wrapper">
              <select
                id="endDate"
                class="form-control"
                @change="
                  addFilter({ type: 'endDate', value: $event.target.value });
                  $event.target.value = null;
                "
              >
                <option></option>
                <option v-for="year in years" :value="year + '-01-01'">
                  {{ year }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_TOPICS">
          <div class="form-group">
            <label for="topic">Schwerpunkte</label>
            <div class="select-wrapper">
              <select
                id="topic"
                class="form-control"
                @change="
                  addFilter({ type: 'topic', value: $event.target.value });
                  $event.target.value = null;
                "
              >
                <option></option>
                <option
                  v-for="topic in topics.filter(
                    (topic) => !topic.context || topic.context === 'project'
                  )"
                >
                  {{ topic.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_PROGRAMS">
          <div class="form-group">
            <label for="program">Programm</label>
            <div class="select-wrapper">
              <select
                id="program"
                class="form-control"
                @change="
                  addFilter({ type: 'program', value: $event.target.value });
                  $event.target.value = null;
                "
              >
                <option></option>
                <option
                  v-for="program in programs.filter(
                    (program) => !program.context || program.context === 'project'
                  )"
                >
                  {{ program.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_INSTRUMENTS">
          <div class="form-group">
            <label for="instrument">Finanzierung</label>
            <div class="select-wrapper">
              <select
                id="instrument"
                class="form-control"
                @change="
                  addFilter({ type: 'instrument', value: $event.target.value });
                  $event.target.value = null;
                "
              >
                <option></option>
                <option
                  v-for="instrument in instruments.filter(
                    (instrument) =>
                      !instrument.context || instrument.context === 'project'
                  )"
                >
                  {{ instrument.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_STATES">
          <div class="form-group">
            <label for="state">Projektregion</label>
            <div class="select-wrapper">
              <select
                id="state"
                class="form-control"
                @change="
                  addFilter({ type: 'state', value: $event.target.value });
                  $event.target.value = null;
                "
              >
                <option></option>
                <option value="austria-wide">Österreichweit</option>
                <option
                  v-for="state in states.filter(
                    (state) => !state.context || state.context === 'project'
                  )"
                >
                  {{ state.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            <label for="localWorkgroup">Lokale Arbeitsgruppe (LAG)</label>
            <div class="select-wrapper">
              <select
                id="localWorkgroup"
                class="form-control"
                @change="
                  addFilter({ type: 'localWorkgroup', value: $event.target.value });
                  $event.target.value = null;
                "
              >
                <option></option>
                <option v-for="workgroup in localWorkgroups" :value="workgroup.name">
                  {{ workgroup.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_GEOGRAPHIC_REGIONS">
          <div class="form-group">
            <label for="geographicRegion">Geographische Region</label>
            <div class="select-wrapper">
              <select
                id="geographicRegion"
                class="form-control"
                @change="
                  addFilter({
                    type: 'geographicRegion',
                    value: $event.target.value,
                  });
                  $event.target.value = null;
                "
              >
                <option></option>
                <option
                  v-for="geographicRegion in geographicRegions.filter(
                    (geographicRegion) =>
                      !geographicRegion.context || geographicRegion.context === 'project'
                  )"
                >
                  {{ geographicRegion.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
        <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_BUSINESS_SECTORS">
          <div class="form-group">
            <label for="businessSector">Geschäftsfeld</label>
            <div class="select-wrapper">
              <select
                id="businessSector"
                class="form-control"
                @change="
                  addFilter({
                    type: 'businessSector',
                    value: $event.target.value,
                  });
                  $event.target.value = null;
                "
              >
                <option></option>
                <option
                  v-for="businessSector in businessSectors.filter(
                    (businessSector) =>
                      !businessSector.context || businessSector.context === 'project'
                  )"
                >
                  {{ businessSector.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Period Filter -->
        <div class="col-sm-3 form-group">
          <label for="lePeriod">LE Periode</label>
          <select
            id="lePeriod"
            class="form-control"
            v-model="selectedPeriod"
            @change="
              addFilter({
                type: 'lePeriod',
                value: { id: selectedPeriod.id, name: selectedPeriod.name },
              })
            "
          >
            <option value="" disabled>Select a period</option>
            <option
              v-for="period in leStructure"
              :key="period.id"
              :value="{ id: period.id, name: period.name }"
            >
              {{ period.name }}
            </option>
          </select>
        </div>

        <!-- Category Filter -->
        <div class="col-sm-3 form-group" v-if="selectedPeriod">
          <label for="leFundingCategory">LE Kategorie</label>
          <select
            id="leFundingCategory"
            class="form-control"
            v-model="selectedCategory"
            @change="addFilter({ type: 'leFundingCategory', value: selectedCategory })"
          >
            <option value="" disabled>Select a category</option>
            <option
              v-for="category in getPeriodById(selectedPeriod.id)?.categories || []"
              :key="category.id"
              :value="{ id: category.id, name: category.name }"
            >
              {{ category.name }}
            </option>
          </select>
        </div>

        <!-- Article Filter -->
        <div class="col-sm-3 form-group" v-if="selectedCategory">
          <label for="leFundingArticle">LE Artikel</label>
          <select
            id="leFundingArticle"
            class="form-control"
            v-model="selectedArticle"
            @change="addFilter({ type: 'leFundingArticle', value: selectedArticle })"
          >
            <option value="" disabled>Select an article</option>
            <option
              v-for="article in getCategoryById(selectedCategory.id)?.articles || []"
              :key="article.id"
              :value="{ id: article.id, name: article.name }"
            >
              {{ article.name }}
            </option>
          </select>
        </div>

        <!-- Method Filter -->
        <div class="col-sm-3 form-group" v-if="selectedArticle">
          <label for="leFundingMethod">LE Handlungsmethode</label>
          <select
            id="leFundingMethod"
            class="form-control"
            v-model="selectedMethod"
            @change="addFilter({ type: 'leFundingMethod', value: selectedMethod })"
          >
            <option value="" disabled>Select a method</option>
            <option
              v-for="method in getArticleById(selectedArticle.id)?.methods || []"
              :key="method.id"
              :value="{ id: method.id, name: method.name }"
            >
              {{ method.name }}
            </option>
          </select>
        </div>
      </div>

      <div class="projects-component-filter-tags">
        <div
          class="tag"
          v-for="filter of filters"
          @click="removeFilter({ type: filter.type, value: filter.value })"
        >
          <strong v-if="filter.type === 'status'">Status:</strong>
          <strong v-if="filter.type === 'startDate'">Start:</strong>
          <strong v-if="filter.type === 'endDate'">Ende:</strong>
          <strong v-if="filter.type === 'topic'">Thema:</strong>
          <strong v-if="filter.type === 'program'">Programm:</strong>
          <strong v-if="filter.type === 'instrument'">Finanzierung:</strong>
          <strong v-if="filter.type === 'state'">Region:</strong>
          <strong v-if="filter.type === 'geographicRegion'">Geographische Region:</strong>
          <strong v-if="filter.type === 'businessSector'">Geschäftsfeld:</strong>
          <!-- <strong v-if="filter.type === 'localWorkgroup'">LAG:</strong> -->
          <template v-if="['startDate', 'endDate'].includes(filter.type)">
            &nbsp;{{ formatDate(filter.value, "YYYY") }}
          </template>
          <template v-else-if="['status'].includes(filter.type)">
            &nbsp;{{ filter.value === "public" ? "Öffentlich" : "Entwurf" }}
          </template>
          <template v-else>
            &nbsp;{{ filter.value.name ? filter.value.name : filter.value.id }}</template
          >
        </div>
      </div>
    </div>

    <div class="projects-component-content">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Code</th>
            <th>Titel</th>
            <th></th>
            <th v-if="$env.PROJECTS_ENABLE_START_DATE">Start</th>
            <th v-if="$env.PROJECTS_ENABLE_END_DATE">Ende</th>
            <th v-if="$env.PROJECTS_ENABLE_TOPICS">Schwerpunkte</th>
            <th v-if="$env.PROJECTS_ENABLE_PROGRAMS">Programm</th>
            <th v-if="$env.PROJECTS_ENABLE_INSTRUMENTS">Finanzierung</th>
            <th>LAG</th>
            <th v-if="$env.PROJECTS_ENABLE_STATES">Region</th>
            <th v-if="$env.PROJECTS_ENABLE_BUSINESS_SECTORS">Geschäftsfelder</th>
            <th>Erstellt</th>
            <th>Geändert</th>
          </tr>
        </thead>
        <tbody v-if="!projects.length && isLoading('projects')">
          <tr>
            <td colspan="11"><em>Projekte werden geladen...</em></td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr
            v-for="project in projects"
            class="clickable"
            :class="{ warning: !project.isPublic }"
            @click="clickProject(project)"
          >
            <td>{{ project.id }}</td>
            <td>{{ project.projectCode || "-" }}</td>
            <td>{{ translateField(project, "title") }}</td>
            <td>
              <span
                v-if="project.caseStudy"
                class="case-study-icon"
                style="margin-left: 8px"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="22"
                  height="22"
                  fill="currentColor"
                  class="bi bi-star-fill"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M3.612 15.443 4.2 10.73.798 7.073l4.824-.696L8 1.288l2.378 5.089 4.824.696-3.402 3.656.588 4.712L8 13.187l-4.388 2.256z"
                  />
                </svg>
              </span>
            </td>
            <td v-if="$env.PROJECTS_ENABLE_START_DATE">
              {{ project.startDate ? project.startDate.substr(0, 4) : "" }}
            </td>
            <td v-if="$env.PROJECTS_ENABLE_END_DATE">
              {{ project.endDate ? project.endDate.substr(0, 4) : "" }}
            </td>
            <td v-if="$env.PROJECTS_ENABLE_TOPICS">
              <div class="tags">
                <span
                  v-for="topic in project.topics"
                  :key="topic.id"
                  class="tag topic-tag"
                >
                  {{ getTopicById(topic.id).name }}
                </span>
              </div>
            </td>
            <td v-if="$env.PROJECTS_ENABLE_PROGRAMS">
              {{ formatOneToMany(project.programs, getProgramById) }}
            </td>
            <td v-if="$env.PROJECTS_ENABLE_INSTRUMENTS">
              {{ formatOneToMany(project.instruments, getInstrumentById) }}
            </td>
            <td>
              {{
                project.localWorkgroup
                  ? getLocalWorkgroupById(project.localWorkgroup.id)?.name
                  : ""
              }}
            </td>
            <td v-if="$env.PROJECTS_ENABLE_STATES">
              <div class="tags">
                <span
                  v-if="project.states.length < 9"
                  v-for="state in project.states"
                  :key="state.id"
                  class="tag state-tag"
                >
                  {{ getStateById(state.id).name }}
                </span>
                <span v-if="project.states.length === 9" class="tag state-tag austria-tag"
                  >Österreichweit</span
                >
              </div>
            </td>
            <td v-if="$env.PROJECTS_ENABLE_BUSINESS_SECTORS">
              {{ formatOneToMany(project.businessSectors, getBusinessSectorById) }}
            </td>
            <td>
              {{ project.createdAt ? $helpers.formatDateTime(project.createdAt) : "-" }}
            </td>
            <td>
              {{ project.updatedAt ? $helpers.formatDateTime(project.updatedAt) : "-" }}
            </td>
          </tr>
        </tbody>
      </table>

      <br /><a @click="clickLoadMore()" class="button" v-if="!isLoadedFully"
        >Mehr Projekte laden</a
      >
    </div>
  </div>
</template>

<script>
import { mapGetters, mapState } from "vuex";
import moment from "moment";
import { translateField } from "../utils/filters";

export default {
  data() {
    return {
      projects: [],
      term: "",
      filters: [],
      limit: 100,
      offset: 0,
      isLoadedFully: false,
      caseStudy: false,
      selectedPeriod: null,
      selectedCategory: null,
      selectedArticle: null,
      selectedMethod: null,
    };
  },
  computed: {
    ...mapState({
      topics: (state) => state.topics.all,
      programs: (state) => state.programs.all,
      instruments: (state) => state.instruments.all,
      states: (state) => state.states.all,
      geographicRegions: (state) => state.geographicRegions.all,
      businessSectors: (state) => state.businessSectors.all,
      localWorkgroups: (state) => state.localWorkgroups.all,
      leStructure: (state) => state.leStructure.all,
    }),
    ...mapGetters({
      isLoading: "loaders/isLoading",
      getTopicById: "topics/getById",
      getProgramById: "programs/getById",
      getInstrumentById: "instruments/getById",
      getStateById: "states/getById",
      getGeographicRegionById: "geographicRegions/getById",
      getBusinessSectorById: "businessSectors/getById",
      getLocalWorkgroupById: "localWorkgroups/getById",
      getPeriodById: "leStructure/getPeriodById",
      getCategoryById: "leStructure/getCategoryById",
      getArticleById: "leStructure/getArticleById",
      getMethodById: "leStructure/getMethodById",
    }),
    years() {
      let years = [];
      let now = moment().startOf("year");

      for (let i = 30; i > 0; i--) {
        years.push(now.format("YYYY"));
        now = moment(now).subtract(1, "year");
      }

      return years;
    },
  },
  methods: {
    translateField,
    changeForm() {
      this.saveFilter();
      this.reloadProjects();
    },
    getFilterParams() {
      let params = {};
      params.term = this.term;
      params.caseStudy = this.caseStudy ? 1 : 0;

      this.filters.forEach((filter) => {
        if (!params[filter.type]) {
          params[filter.type] = [];
        }
        params[filter.type].push(filter.value.id);
      });

      if (this.selectedPeriod) {
        params.lePeriod = [this.selectedPeriod.id];
      }
      if (this.selectedCategory) {
        params.leFundingCategory = [this.selectedCategory.id];
      }
      if (this.selectedArticle) {
        params.leFundingArticle = [this.selectedArticle.id];
      }
      if (this.selectedMethod) {
        params.leFundingMethod = [this.selectedMethod.id];
      }

      params.limit = this.limit;
      params.offset = this.offset;
      params.orderBy = ["id"];
      params.orderDirection = ["DESC"];

      return params;
    },

    reloadProjects() {
      this.isLoadedFully = false;
      this.offset = 0;
      return this.$store
        .dispatch("projects/loadFiltered", this.getFilterParams())
        .then((projects) => {
          this.projects = [...projects];
        });
    },
    clickLoadMore() {
      this.offset += this.limit;
      this.$store
        .dispatch("projects/loadFiltered", this.getFilterParams())
        .then((projects) => {
          if (!projects.length) {
            this.isLoadedFully = true;
          }
          this.projects = [...this.projects, ...projects];
        });
    },
    clickProject(project) {
      this.$router.push({
        path: "/projects/" + project.id + "/edit",
      });
    },
    formatOneToMany(items, getter) {
      let result = [];
      items.forEach((item) => {
        result.push(getter(item.id)?.name);
      });

      return result.join(", ");
    },
    formatDate(date, format = "DD.MM.YYYY") {
      if (date && moment(date)) {
        return moment(date).format(format);
      }
    },
    addFilter(filter) {
      if (!filter.value) {
        return;
      }

      const filterValue = {
        id:
          filter.value === "austria-wide"
            ? "austria-wide"
            : filter.value.id
            ? filter.value.id
            : filter.value,
        name:
          filter.value === "austria-wide"
            ? "Österreichweit"
            : filter.value.name
            ? filter.value.name
            : filter.value,
      };

      const existingFilterIndex = this.filters.findIndex((f) => f.type === filter.type);
      if (existingFilterIndex !== -1) {
        this.filters.splice(existingFilterIndex, 1);
      }

      if (filter.type === "lePeriod") {
        this.selectedPeriod = filter.value;
        this.selectedCategory = null;
        this.selectedArticle = null;
        this.selectedMethod = null;

        this.filters = this.filters.filter(
          (f) =>
            f.type !== "leFundingCategory" &&
            f.type !== "leFundingArticle" &&
            f.type !== "leFundingMethod"
        );
      } else if (filter.type === "leFundingCategory") {
        this.selectedCategory = filter.value;
        this.selectedArticle = null;
        this.selectedMethod = null;

        this.filters = this.filters.filter(
          (f) => f.type !== "leFundingArticle" && f.type !== "leFundingMethod"
        );
      } else if (filter.type === "leFundingArticle") {
        this.selectedArticle = filter.value;
        this.selectedMethod = null;

        this.filters = this.filters.filter((f) => f.type !== "leFundingMethod");
      } else if (filter.type === "leFundingMethod") {
        this.selectedMethod = filter.value;
      }

      if (
        this.filters
          .filter((f) => f.type === filter.type)
          .find((f) => f.value.id === filterValue.id)
      ) {
        return;
      }

      this.filters.push({
        type: filter.type,
        value: filterValue,
      });

      this.changeForm();
    },

    removeFilter(filter) {
      let f = this.filters.find(
        (f) => f.type === filter.type && f.value.id === filter.value.id
      );
      if (f) {
        this.filters.splice(this.filters.indexOf(f), 1);
      }

      if (filter.type === "lePeriod") {
        this.selectedPeriod = null;
        this.selectedCategory = null;
        this.selectedArticle = null;
        this.selectedMethod = null;

        this.filters = this.filters.filter(
          (f) =>
            f.type !== "leFundingCategory" &&
            f.type !== "leFundingArticle" &&
            f.type !== "leFundingMethod"
        );
      }

      if (filter.type === "leFundingCategory") {
        this.selectedCategory = null;
        this.selectedArticle = null;
        this.selectedMethod = null;

        this.filters = this.filters.filter(
          (f) => f.type !== "leFundingArticle" && f.type !== "leFundingMethod"
        );
      }

      if (filter.type === "leFundingArticle") {
        this.selectedArticle = null;
        this.selectedMethod = null;

        this.filters = this.filters.filter((f) => f.type !== "leFundingMethod");
      }

      if (filter.type === "leFundingMethod") {
        this.selectedMethod = null;
      }

      this.changeForm();
    },

    saveFilter() {
      window.sessionStorage.setItem(
        "regiosuisse.projects.filters",
        JSON.stringify(this.filters)
      );
      window.sessionStorage.setItem("regiosuisse.projects.term", this.term);
      window.sessionStorage.setItem("regiosuisse.projects.caseStudy", this.caseStudy);
      window.sessionStorage.setItem(
        "regiosuisse.projects.localWorkgroup",
        this.localWorkgroup
      );
    },
    loadFilter() {
      this.filters = JSON.parse(
        window.sessionStorage.getItem("regiosuisse.projects.filters") || "[]"
      );
      this.term = window.sessionStorage.getItem("regiosuisse.projects.term") || "";
      this.caseStudy = JSON.parse(
        window.sessionStorage.getItem("regiosuisse.projects.caseStudy") || "false"
      );
    },
    loadLeStructure() {
      this.$store.dispatch("leStructure/loadAll");
    },
  },
  created() {
    this.loadLeStructure();
    this.loadFilter();
    this.reloadProjects();
  },
};
</script>

<style scoped>
.tags {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
}

.tag {
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 12px;
  display: inline-block;
}

.topic-tag {
  background-color: #5077b2;
  /* Light grey background for topics */
  border: 1px solid #6297e7;
  /* Light grey border */
  color: #fff;
  /* White text */
}

.state-tag {
  background-color: #fff;
  /* White background for states */
  border: 1px solid #000;
  /* Black border */
  color: #000;
  /* Black text */
}

.case-study-icon {
  height: 25px;
  max-height: 25px;
  width: 25px;
  max-width: 25px;
  background-color: #5077b2;
  /* Blue background */
  color: white;
  /* White text */
  border-radius: 50%;
  /* Circular shape */
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 12px;
  text-align: center;

  svg {
    width: 15px;
    height: 15px;
  }
}

.austria-tag {
  background: linear-gradient(
    0deg,
    rgba(255, 0, 0, 0.5) 25%,
    white 33%,
    white 66%,
    rgba(255, 0, 0, 0.5) 75%
  );
  border: 1px solid black;
  color: black;
  text-align: center;
  font-weight: bold;
}
</style>
