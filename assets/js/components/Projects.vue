<template>
  <div class="projects-component">
    <div class="projects-component-title">
      <h2>Projekte</h2>

      <transition name="fade" mode="out-in">
        <div class="loading-indicator" v-if="isLoading('projects')"></div>
      </transition>

      <div class="projects-component-title-actions">
        <a href="/api/v1/projects.xlsx" class="button" download>XLSX</a>
        <router-link :to="'/projects/import'" class="button">Projekte importieren</router-link>
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
        <!-- <div class="col-sm-3">
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
        </div> -->

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
        <!-- LE Structure Filters (Period, Category, Article, Method) -->
        <div class="col-sm-3">
          <div class="form-group">
            <label for="lePeriod">LE-Periode</label>
            <enhanced-select
              v-model="selectedPeriod"
              :options="leStructure"
              placeholder="LE-Periode auswählen"
              @change="handleLEPeriodChange"
            />
          </div>
        </div>

        <div class="col-sm-3" v-if="selectedPeriod">
          <div class="form-group">
            <label for="leFundingCategory">LE Kategorie</label>
            <enhanced-select
              v-model="selectedCategory"
              :options="getPeriodById(selectedPeriod.id)?.categories || []"
              placeholder="Kategorie auswählen"
              @change="handleLECategoryChange"
            />
          </div>
        </div>

        <div class="col-sm-3" v-if="selectedCategory">
          <div class="form-group">
            <label for="leFundingArticle">LE Artikel</label>
            <enhanced-select
              v-model="selectedArticle"
              :options="getCategoryById(selectedCategory.id)?.articles || []"
              placeholder="Artikel auswählen"
              @change="handleLEArticleChange"
            />
          </div>
        </div>

        <div class="col-sm-3" v-if="selectedArticle">
          <div class="form-group">
            <label for="leFundingMethod">LE Handlungsmethode</label>
            <enhanced-select
              v-model="selectedMethod"
              :options="getArticleById(selectedArticle.id)?.methods || []"
              placeholder="Methode auswählen"
              @change="handleLEMethodChange"
            />
          </div>
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
            <!-- Bulk selection checkbox column -->
            <th style="width: 40px;">
              <input 
                type="checkbox" 
                :checked="isAllSelected"
                :class="{ 'indeterminate': isPartiallySelected }"
                @change="toggleSelectAll"
                class="bulk-select-checkbox"
              />
            </th>
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
            <td colspan="16"><em>Projekte werden geladen...</em></td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr
            v-for="project in projects"
            class="clickable"
            :class="{ warning: !project.isPublic, 'selected-row': isProjectSelected(project.id) }"
            @click="clickProject(project)"
          >
            <!-- Bulk selection checkbox -->
            <td @click.stop>
              <input 
                type="checkbox" 
                :checked="isProjectSelected(project.id)"
                @change="toggleProjectSelection(project.id)"
                class="project-select-checkbox"
              />
            </td>
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

    <!-- Fixed Position Delete Button -->
    <div 
      v-if="hasSelectedProjects" 
      class="bulk-delete-button"
      @click="openDeleteModal"
    >
      <span class="material-icons">delete</span>
      Löschen ({{ selectedProjects.length }})
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div 
      v-if="showDeleteModal" 
      class="project-component-overlay"
      @click="closeDeleteModal"
    >
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Projekte löschen</h3>
          <button @click="closeDeleteModal" class="modal-close">
            <span class="material-icons">close</span>
          </button>
        </div>
        
        <div class="modal-body">
          <p>Sind Sie sicher, dass Sie die folgenden Projekte unwiderruflich löschen möchten?</p>
          
          <div class="projects-to-delete">
            <div 
              v-for="project in projectsToDelete" 
              :key="project.id"
              class="project-item"
              :class="{ 'deselected': !project.selected }"
            >
              <input 
                type="checkbox" 
                :checked="project.selected"
                @change="toggleProjectInModal(project.id)"
                class="project-checkbox"
              />
              <div class="project-info">
                <strong>{{ project.title }}</strong>
                <span v-if="project.projectCode" class="project-code">
                  ({{ project.projectCode }})
                </span>
                <small class="project-id">ID: {{ project.id }}</small>
              </div>
            </div>
          </div>
          
          <p v-if="getSelectedProjectsCount() > 0" class="deletion-count">
            <strong>{{ getSelectedProjectsCount() }} Projekt(e) werden gelöscht.</strong>
          </p>
          <p v-else class="no-selection">
            Kein Projekt ausgewählt.
          </p>
        </div>
        
        <div class="modal-actions">
          <button 
            @click="closeDeleteModal" 
            class="button secondary"
          >
            Abbrechen
          </button>
          <button 
            @click="confirmBulkDelete" 
            class="button error"
            :disabled="getSelectedProjectsCount() === 0"
          >
            {{ getSelectedProjectsCount() > 0 ? `${getSelectedProjectsCount()} Projekt(e) löschen` : 'Löschen' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapState } from "vuex";
import moment from "moment";
import { translateField } from "../utils/filters";
import EnhancedSelect from "./EnhancedSelect.vue";
import axios from "axios";

export default {
  components: {
    EnhancedSelect
  },
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
      selectedProjects: [],
      showDeleteModal: false,
      projectsToDelete: [],
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
    isAllSelected() {
      return this.projects.length > 0 && this.selectedProjects.length === this.projects.length;
    },
    isPartiallySelected() {
      return this.selectedProjects.length > 0 && this.selectedProjects.length < this.projects.length;
    },
    hasSelectedProjects() {
      return this.selectedProjects.length > 0;
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
      // Clear selections when reloading
      this.selectedProjects = [];
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
    handleLEPeriodChange(period) {
      this.addFilter({
        type: 'lePeriod',
        value: period,
      });
    },
    handleLECategoryChange(category) {
      this.addFilter({
        type: 'leFundingCategory',
        value: category,
      });
    },
    handleLEArticleChange(article) {
      this.addFilter({
        type: 'leFundingArticle',
        value: article,
      });
    },
    handleLEMethodChange(method) {
      this.addFilter({
        type: 'leFundingMethod',
        value: method,
      });
    },
    // =====================================
    // Bulk Delete Methods
    // =====================================
    
    // Toggle selection of all projects
    toggleSelectAll() {
      if (this.isAllSelected) {
        this.selectedProjects = [];
      } else {
        this.selectedProjects = [...this.projects.map(p => p.id)];
      }
    },
    
    // Toggle selection of individual project
    toggleProjectSelection(projectId) {
      const index = this.selectedProjects.indexOf(projectId);
      if (index > -1) {
        this.selectedProjects.splice(index, 1);
      } else {
        this.selectedProjects.push(projectId);
      }
    },
    
    // Check if project is selected
    isProjectSelected(projectId) {
      return this.selectedProjects.includes(projectId);
    },
    
    // Open delete confirmation modal
    openDeleteModal() {
      // Create a copy of selected projects for the modal
      this.projectsToDelete = this.projects.filter(project => 
        this.selectedProjects.includes(project.id)
      ).map(project => ({
        id: project.id,
        title: project.title,
        projectCode: project.projectCode,
        selected: true
      }));
      this.showDeleteModal = true;
    },
    
    // Close delete modal and reset
    closeDeleteModal() {
      this.showDeleteModal = false;
      this.projectsToDelete = [];
    },
    
    // Toggle project selection in delete modal
    toggleProjectInModal(projectId) {
      const index = this.projectsToDelete.findIndex(p => p.id === projectId);
      if (index !== -1) {
        // Create a new object to trigger reactivity in Vue 3
        this.projectsToDelete[index] = {
          ...this.projectsToDelete[index],
          selected: !this.projectsToDelete[index].selected
        };
      }
    },
    
    // Confirm bulk delete
    async confirmBulkDelete() {
      const projectIdsToDelete = this.projectsToDelete
        .filter(p => p.selected)
        .map(p => p.id);
      
      if (projectIdsToDelete.length === 0) {
        this.closeDeleteModal();
        return;
      }
      
      try {
        // Delete projects one by one (assuming no bulk delete endpoint exists)
        for (const projectId of projectIdsToDelete) {
          await this.$store.dispatch('projects/delete', projectId);
        }
        
        // Remove deleted projects from local state
        this.projects = this.projects.filter(project => 
          !projectIdsToDelete.includes(project.id)
        );
        
        // Clear selection
        this.selectedProjects = [];
        this.closeDeleteModal();
        
        // Show success message or reload if needed
        // this.reloadProjects(); // Uncomment if you want to reload from server
        
      } catch (error) {
        console.error('Error deleting projects:', error);
        // Handle error - could show error modal here
      }
    },
    
    // Get selected projects count for modal
    getSelectedProjectsCount() {
      return this.projectsToDelete.filter(p => p.selected).length;
    },
  },
  created() {
    this.loadLeStructure();
    this.loadFilter();
    this.reloadProjects();
  },
  watch: {
    // Handle indeterminate state for select-all checkbox
    isPartiallySelected(newVal) {
      this.$nextTick(() => {
        const checkbox = this.$el.querySelector('.bulk-select-checkbox');
        if (checkbox) {
          checkbox.indeterminate = newVal;
        }
      });
    },
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

/* Add some spacing between the filter rows */
.row {
  margin-bottom: 15px;
}

/* Ensure form groups have consistent spacing */
.form-group {
  margin-bottom: 1rem;
}

/* Style labels consistently */
label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

/* =====================================
 * Bulk Delete Functionality Styles
 * ===================================== */

/* Checkbox Styling */
.bulk-select-checkbox,
.project-select-checkbox {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #5077b2;
}

/* Indeterminate state for select all checkbox */
.bulk-select-checkbox.indeterminate {
  opacity: 0.6;
}

/* Selected row highlighting */
.selected-row {
  background-color: rgba(80, 119, 178, 0.1) !important;
}

.selected-row:hover {
  background-color: rgba(80, 119, 178, 0.2) !important;
}

/* Fixed Position Delete Button */
.bulk-delete-button {
  position: fixed;
  bottom: 30px;
  right: 30px;
  background-color: #dc3545;
  color: white;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  font-size: 16px;
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
  z-index: 1000;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  border: none;
  user-select: none;
}

.bulk-delete-button:hover {
  background-color: #c82333;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
}

.bulk-delete-button:active {
  transform: translateY(0);
}

.bulk-delete-button .material-icons {
  font-size: 20px;
}

/* Modal Overlay (reusing existing Project.vue modal styles) */
.project-component-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

/* Modal Content */
.modal-content {
  background: white;
  border-radius: 8px;
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

/* Modal Header */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #eee;
}

.modal-header h3 {
  margin: 0;
  color: #333;
  font-size: 20px;
  font-weight: 600;
}

.modal-close {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  color: #666;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.modal-close:hover {
  background-color: #f5f5f5;
  color: #333;
}

.modal-close .material-icons {
  font-size: 24px;
}

/* Modal Body */
.modal-body {
  padding: 24px;
}

.modal-body p {
  margin-bottom: 20px;
  color: #555;
  line-height: 1.5;
}

/* Projects to Delete List */
.projects-to-delete {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  margin: 20px 0;
}

.project-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #f0f0f0;
  transition: all 0.2s ease;
  background-color: #fff;
}

.project-item:last-child {
  border-bottom: none;
}

.project-item:hover {
  background-color: #f8f9fa;
}

.project-item.deselected {
  opacity: 0.5;
  background-color: #f9f9f9;
}

.project-item .project-checkbox {
  margin-right: 12px;
  width: 16px;
  height: 16px;
  accent-color: #5077B2;
}

.project-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.project-info strong {
  color: #333;
  font-size: 14px;
}

.project-code {
  color: #666;
  font-size: 13px;
}

.project-id {
  color: #999;
  font-size: 12px;
}

/* Count Information */
.deletion-count {
  color: #5077B2;
  font-weight: 500;
  margin-top: 16px;
  margin-bottom: 0;
}

.no-selection {
  color: #6c757d;
  font-style: italic;
  margin-top: 16px;
  margin-bottom: 0;
}

/* Modal Actions */
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px 24px;
  border-top: 1px solid #eee;
  background-color: #fafafa;
}

.modal-actions .button {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  font-size: 14px;
  transition: all 0.2s ease;
  text-decoration: none;
  display: inline-block;
}

.modal-actions .button.secondary {
  background-color: #6c757d;
  color: white;
}

.modal-actions .button.secondary:hover {
  background-color: #5a6268;
}

.modal-actions .button.error {
  background-color: #5077B2;
  color: white;
}

.modal-actions .button.error:hover:not(:disabled) {
  background-color: #0056b3;
}

.modal-actions .button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.modal-actions .button:disabled:hover {
  background-color: #5077B2;
  transform: none;
}
</style>
