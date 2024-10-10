<template>
  <div class="embed-projects-view" :class="$env.INSTANCE_ID + '-projects-view'">
    <div class="embed-projects-view-header">
      <h1 class="embed-projects-view-header-title">
        {{ translateField(project, "title", locale) }}
      </h1>

      <a class="embed-projects-view-header-close" @click="clickClose()"></a>
    </div>

    <div class="embed-projects-view-content">
      <div
        class="embed-projects-view-content-text"
        v-html="translateField(project, 'description', locale)"
      ></div>

      <div v-if="project.exemplary && project.caseStudy">
        <h4 class="nzl-title">Was macht dieses Projekt besonders nachahmenswert?</h4>
        <div class="embed-projects-view-content-text" v-html="project.exemplary"></div>
      </div>

      <div v-if="project.initialContext && project.caseStudy">
        <h4 class="nzl-title">Kontext</h4>
        <div
          class="embed-projects-view-content-text"
          v-html="project.initialContext"
        ></div>
      </div>

      <div v-if="project.initialContextGoals && project.caseStudy">
        <h4 class="nzl-title">Ziele</h4>
        <div
          class="embed-projects-view-content-text"
          v-html="project.initialContextGoals"
        ></div>
      </div>

      <div v-if="project.fundingMethod && project.caseStudy">
        <h4 class="nzl-title">Maßnahmen im Projekt</h4>
        <div
          class="embed-projects-view-content-text"
          v-html="project.fundingMethod"
        ></div>
      </div>

      <div v-if="project.fundingMethodStakeholders && project.caseStudy">
        <h4 class="nzl-title">Welche Stakeholder waren entscheidend?</h4>
        <div
          class="embed-projects-view-content-text"
          v-html="project.fundingMethodStakeholders"
        ></div>
      </div>

      <div v-if="project.resultsQuantity && project.caseStudy">
        <h4 class="nzl-title">Ergebnisse und Wirkungen (Quantitativ)</h4>
        <div
          class="embed-projects-view-content-text"
          v-html="project.resultsQuantity"
        ></div>
      </div>

      <div v-if="project.resultsQuality && project.caseStudy">
        <h4 class="nzl-title">Ergebnisse und Wirkungen (Qualitativ)</h4>
        <div
          class="embed-projects-view-content-text"
          v-html="project.resultsQuality"
        ></div>
      </div>

      <div v-if="project.additionalValue && project.caseStudy">
        <h4 class="nzl-title">Mehrwert durch Vernetzung</h4>
        <div
          class="embed-projects-view-content-text"
          v-html="project.additionalValue"
        ></div>
      </div>

      <div v-if="project.additionalValueResult && project.caseStudy">
        <h4 class="nzl-title">Mehrwert durch Vernetzung Ergebnisse</h4>
        <div
          class="embed-projects-view-content-text"
          v-html="project.additionalValueResult"
        ></div>
      </div>

      <div v-if="project.innovations && project.caseStudy">
        <h4 class="nzl-title">Innovation</h4>
        <div class="embed-projects-view-content-text" v-html="project.innovations"></div>
      </div>

      <div v-if="project.integrationYoungCitizen && project.caseStudy">
        <h4 class="nzl-title">Einbeziehung junger Menschen</h4>
        <div
          class="embed-projects-view-content-text"
          v-html="project.integrationYoungCitizen"
        ></div>
      </div>

      <div v-if="project.integrationFemaleCitizen && project.caseStudy">
        <h4 class="nzl-title">Einbeziehung von Frauen</h4>
        <div
          class="embed-projects-view-content-text"
          v-html="project.integrationFemaleCitizen"
        ></div>
      </div>

      <div v-if="project.integrationMinorities && project.caseStudy">
        <h4 class="nzl-title">Inklusion</h4>
        <div
          class="embed-projects-view-content-text"
          v-html="project.integrationMinorities"
        ></div>
      </div>

      <div v-if="project.learningExperience && project.caseStudy">
        <h4 class="nzl-title">Die wichtigsten Lernerfahrungen</h4>
        <div
          class="embed-projects-view-content-text"
          v-html="project.learningExperience"
        ></div>
      </div>

      <div v-if="project.synergyFundTags.length > 0 && project.caseStudy">
        <h4 class="nzl-title">Synergien mit anderen EU-Politiken</h4>
        <span>(GAP und andere EU Förderquellen)</span>
        <ul class="synergy-list" v-html="synergyFundTagsHTML"></ul>
      </div>

      <div v-if="project.synergyGoalTags.length > 0 && project.caseStudy">
        <h4 class="nzl-title">Europäische und Internationale Politik</h4>
        <span
          >Dieses Projekt trägt zu Zielen folgenden europäischen und internationalen
          Politiken bei:</span
        ><br />
        <ul class="synergy-list" v-html="synergyGoalTagsHTML"></ul>
      </div>

      <div v-if="project.transferable && project.caseStudy">
        <h4 class="nzl-title">Übertragbarkeit</h4>
        <div class="embed-projects-view-content-text" v-html="project.transferable"></div>
      </div>

      <div v-if="project.transferDetails && project.caseStudy">
        <h4 class="nzl-title">Dieses Projekt wurde kopiert von:</h4>
        <div
          class="embed-projects-view-content-text"
          v-html="project.transferDetails"
        ></div>
      </div>

      <div
        class="embed-projects-view-content-downloads"
        v-if="translateField(project, 'files', locale)?.length"
      >
        <h4 class="nzl-title">{{ $t("Downloads", locale) }}</h4>
        <div
          class="embed-projects-view-content-downloads-download"
          v-for="(file, index) in translateField(project, 'files', locale)"
        >
          <a
            :href="$env.HOST + '/api/v1/files/download/' + file.id + '.' + file.extension"
            download
          >
            {{ file.description || "Datei " + (index + 1) }}
          </a>
        </div>
      </div>

      <div
        class="embed-projects-view-content-contacts"
        v-if="translateField(project, 'contacts', locale)?.length"
      >
        <h4 class="nzl-title">{{ $t("Kontakt", locale) }}</h4>
        <div
          class="embed-projects-view-content-contacts-contact"
          v-for="contact in translateField(project, 'contacts', locale)"
        >
          <p>
            <template v-if="contact.name">
              <strong>{{ contact.name }}</strong
              ><br />
            </template>
            <template v-if="contact.firstName && contact.lastName">
              {{ contact.title || "" }} {{ contact.firstName }} {{ contact.lastName
              }}<br />
            </template>
            <template v-if="contact.role"> {{ contact.role }}<br /> </template>
            <template v-if="contact.street"> {{ contact.street }}<br /> </template>
            <template v-if="contact.zipCode && contact.city">
              {{ contact.zipCode || "" }} {{ contact.city || "" }}<br />
            </template>
            <template v-if="contact.phone">
              <a :href="'tel:' + contact.phone">{{ contact.phone }}</a
              ><br />
            </template>
            <template v-if="contact.email">
              <a :href="'mailto:' + contact.email">{{ contact.email }}</a
              ><br />
            </template>
            <template v-if="contact.website && contact.website.startsWith('http')">
              <a :href="contact.website" target="_blank">{{
                contact.website.split("://", 2)[1]
              }}</a
              ><br />
            </template>
            <template v-if="contact.website && !contact.website.startsWith('http')">
              <a :href="'http://' + contact.website" target="_blank">{{
                contact.website
              }}</a
              ><br />
            </template>
          </p>
        </div>
      </div>

      <div
        class="embed-projects-view-content-gallery"
        v-if="(translateField(project, 'images', locale) || []).length > 1"
      >
        <div
          class="embed-projects-view-content-gallery-image"
          v-for="image in (translateField(project, 'images', locale) || []).slice(1)"
        >
          <a @click="clickShowImage(image)">
            <img
              :src="$env.HOST + '/api/v1/files/view/' + image.id + '.' + image.extension"
              alt=""
            />
          </a>
        </div>
      </div>

      <div
        class="embed-projects-view-content-videos"
        v-if="(translateField(project, 'videos', locale) || []).length > 1"
      >
        <div
          class="embed-projects-view-content-videos-video"
          v-for="video in translateField(project, 'videos', locale) || []"
        >
          <div class="youtube-embed" v-if="parseYoutubeId(video.url)">
            <iframe
              width="560"
              height="315"
              :src="'https://www.youtube-nocookie.com/embed/' + parseYoutubeId(video.url)"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
            ></iframe>
          </div>
        </div>
      </div>

      <div
        v-if="templateHook('projectContentAfter', project)"
        v-html="templateHook('projectContentAfter', project)"
      ></div>
    </div>

    <div class="embed-projects-view-sidebar">
      <div
        v-if="templateHook('projectSidebarImage', project)"
        v-html="templateHook('projectSidebarImage', project)"
      ></div>

      <template v-else>
        <div
          class="embed-projects-view-sidebar-image"
          v-for="image in (translateField(project, 'images', locale) || []).slice(0, 1)"
        >
          <a @click="clickShowImage(image)">
            <img
              :src="$env.HOST + '/api/v1/files/view/' + image.id + '.' + image.extension"
              alt=""
            />
          </a>
        </div>
      </template>

      <template v-if="statesHTML">
        <h3>{{ $t("Projektregion", locale) }}</h3>
        <div class="embed-tags" v-html="statesHTML"></div>
      </template>

      <template v-if="project.localWorkgroup">
        <h3>{{ $t("Lokale Arbeitsgruppe", locale) }}</h3>
        <div class="embed-tags" v-html="localWorkgroupHTML"></div>
      </template>

      <template v-if="project.localWorkgroups.length > 0">
        <h3>{{ $t("Kooperierende Arbeitsgruppen", locale) }}</h3>
        <div class="embed-tags" v-html="localWorkgroupsHTML"></div
      ></template>

      <template v-if="project.cooperationProjectEu">
        <p>🇪🇺 Dies ist ein EU Kooperationsprojekt</p>
      </template>

      <template v-if="topicsHTML">
        <h3>{{ $t("Schwerpunkte", locale) }}</h3>
        <div class="embed-tags" v-html="topicsHTML"></div>
      </template>

      <template v-if="project.tags.length > 0">
        <h3>{{ $t("Schlagworte", locale) }}</h3>
        <div class="embed-tags" v-html="tagsHTML"></div>
      </template>

      <template v-if="programsHTML">
        <h3>{{ $t("Programm", locale) }}</h3>
        <p v-html="programsHTML"></p>
      </template>

      <template v-if="project.startDate && project.endDate">
        <h3>{{ $t("Projektdauer", locale) }}</h3>
        <p>
          {{ $helpers.formatDate(project.startDate) }} -
          {{ $helpers.formatDate(project.endDate) }}
        </p>
      </template>

      <template v-if="instrumentsHTML">
        <h3>{{ $t("Finanzierung", locale) }}</h3>
        <p v-html="instrumentsHTML"></p>
      </template>

      <template v-if="project.projectCosts">
        <h3>{{ $t("Projektkosten", locale) }}</h3>
        <p>{{ $helpers.formatCurrency(project.projectCosts) }}</p>
        <table class="project-costs-table">
          <tbody>
            <tr v-for="financing in project.financing" :key="financing.id">
              <td v-if="financing.id === 'costsFederation'">
                {{ $t("GAP Strategieplan", locale) }}
              </td>
              <td v-if="financing.id === 'costsCanton'">
                {{ $t("Private und Eigenmittel", locale) }}
              </td>
              <td v-if="financing.id === 'costsExternal'">
                {{ $t("Andere Finanzquellen", locale) }}
              </td>
              <td>{{ $helpers.formatPercent(financing.value) }}</td>
              <td>
                {{ $helpers.formatPercentValue(financing.value, project.projectCosts) }}
              </td>
            </tr>
            <tr>
              <td>Gesamt:</td>
              <td></td>
              <td>{{ $helpers.formatCurrency(project.projectCosts) }}</td>
            </tr>
          </tbody>
        </table>
        <p></p>
      </template>

      <template v-if="linksHTML">
        <h3>{{ $t("Links", locale) }}</h3>
        <p v-html="linksHTML"></p>
      </template>

      <div
        v-if="templateHook('projectSidebarAfter', project)"
        v-html="templateHook('projectSidebarAfter', project)"
      ></div>
    </div>

    <transition name="embed-projects-view-lightbox" mode="out-in">
      <div
        class="embed-projects-view-lightbox"
        v-if="lightboxImage"
        @click="clickHideImage()"
      >
        <div
          class="embed-projects-view-lightbox-content"
          :style="{
            backgroundImage:
              'url(' +
              $env.HOST +
              '/api/v1/files/view/' +
              lightboxImage.id +
              '.' +
              lightboxImage.extension +
              ')',
          }"
        ></div>

        <div
          class="embed-projects-view-lightbox-description"
          @click.stop
          v-if="lightboxImage.description || lightboxImage.copyright"
        >
          <template v-if="lightboxImage.description">{{
            lightboxImage.description
          }}</template>
          <template v-if="lightboxImage.description && lightboxImage.copyright">
            |
          </template>
          <template v-if="lightboxImage.copyright"
            >© {{ lightboxImage.copyright }}</template
          >
        </div>

        <a class="embed-projects-view-lightbox-prev" @click.stop="clickPrevImage()">
          <span class="embed-projects-view-lightbox-prev-icon"></span>
        </a>

        <a class="embed-projects-view-lightbox-next" @click.stop="clickNextImage()">
          <span class="embed-projects-view-lightbox-next-icon"></span>
        </a>
      </div>
    </transition>
  </div>
</template>

<script>
import { mapGetters, mapState } from "vuex";
import { translateField } from "../utils/filters";

export default {
  data() {
    return {
      lightboxImage: null,
    };
  },

  props: {
    locale: {
      type: String,
      default: "de",
      required: false,
    },
    project: {
      type: Object,
      required: true,
    },
  },

  emits: ["clickClose"],
  mounted() {
    this.$store.dispatch("localWorkgroups/loadAll");
    this.$store.dispatch("tags/loadAll");
  },
  computed: {
    ...mapState({
      states: (state) => state.states.all,
      topics: (state) => state.topics.all,
      programs: (state) => state.programs.all,
      instruments: (state) => state.instruments.all,
      localWorkgroups: (state) => state.localWorkgroups.all,
      tags: (state) => state.tags.all,
    }),
    ...mapGetters({
      getStateById: "states/getById",
      getTopicById: "topics/getById",
      getProgramById: "programs/getById",
      getInstrumentById: "instruments/getById",
      getLocalWorkgroupById: "localWorkgroups/getById",
      getTagById: "tags/getById",
    }),
    tagsHTML() {
      let result = [];

      this.project.tags.forEach((item) => {
        let tag = this.getTagById(item.id);
        result.push(`<span class="tag">${tag?.name}</span>`);
      });

      return result.join("");
    },
    synergyFundTagsHTML() {
      let result = [];

      this.project.synergyFundTags.forEach((item) => {
        let tag = this.getTagById(item.id);

        result.push(`<li>${tag?.name}</li>`);
      });

      return result.join("");
    },
    synergyGoalTagsHTML() {
      let result = [];

      this.project.synergyGoalTags.forEach((item) => {
        let tag = this.getTagById(item.id);

        result.push(`<li>${tag?.name}</li>`);
      });

      return result.join("");
    },
    statesHTML() {
      let result = [];
      if (this.project.states.length === 9) {
        return `<span class="tag austria-tag">Österreichweit</span>`;
      }

      this.project.states.forEach((item) => {
        let row = this.translateField(this.getStateById(item.id), "name", this.locale);

        result.push(`<span class="tag">${row}</span>`);
      });

      return result.join("");
    },
    topicsHTML() {
      let result = [];

      this.project.topics.forEach((item) => {
        let row = this.translateField(this.getTopicById(item.id), "name", this.locale);

        result.push(`<span class="tag">${row}</span>`);
      });

      return result.join("");
    },
    localWorkgroupHTML() {
      let result = [];
      try {
        // TODO_MAP: this is a quickfix because for some reason in the iframe the object contains an id and in the backend it does not.
        result = this.getLocalWorkgroupById(
          this.project.localWorkgroup.id
            ? this.project.localWorkgroup.id
            : this.project.localWorkgroup
        );
      } catch (e) {
        console.error(e);
      }
      return `<span class="tag">${result ? result.name : null}</span>`;
    },

    localWorkgroupsHTML() {
      let result = [];

      this.project.localWorkgroups.forEach((item) => {
        let row = this.getLocalWorkgroupById(item.id);
        if (row) {
          result.push(`<span class="tag">${row.name}</span><br />`);
        }
      });

      return `${result.join("")}`;
    },

    programsHTML() {
      let result = [];

      this.project.programs.forEach((item) => {
        let url = this.translateField(this.getProgramById(item.id), "url", this.locale);
        let row =
          this.translateField(this.getProgramById(item.id), "longName", this.locale) ||
          this.translateField(this.getProgramById(item.id), "name", this.locale);

        if (url) {
          row =
            '<a href="' + url + '" target="_blank" title="' + row + '">' + row + "</a>";
        }

        result.push(row);
      });

      return result.join("");
    },
    instrumentsHTML() {
      let result = [];

      this.project.instruments.forEach((item) => {
        let row = this.translateField(
          this.getInstrumentById(item.id),
          "name",
          this.locale
        );

        result.push(row);
      });

      return result.join(", ");
    },
    linksHTML() {
      let result = [];

      translateField(this.project, "links", this.locale).forEach((item) => {
        let url = item.url.split("://").length > 1 ? item.url : "http://" + item.url;

        let row = '<a href="' + url + '" target="_blank">' + item.label + "</a>";

        result.push(row);
      });

      return result.join("<br>");
    },
  },

  methods: {
    translateField,

    templateHook(name, ...params) {
      if (this?.$clientOptions?.templateHooks?.[name]) {
        return this.$clientOptions.templateHooks[name](this, ...params);
      }

      return null;
    },

    clickClose() {
      this.$emit("clickClose");
    },

    clickShowImage(image) {
      this.lightboxImage = image;
    },

    clickHideImage() {
      this.lightboxImage = null;
    },

    clickPrevImage() {
      let images = this.translateField(this.project, "images", this.locale);
      let index = images.findIndex((i) => i.id === this.lightboxImage.id);

      this.lightboxImage = images[index - 1] || images[images.length - 1];
    },

    clickNextImage() {
      let images = this.translateField(this.project, "images", this.locale);
      let index = images.findIndex((i) => i.id === this.lightboxImage.id);

      this.lightboxImage = images[index + 1] || images[0];
    },

    parseYoutubeId(url) {
      const result = (url || "").split(/(vi\/|v=|\/v\/|youtu\.be\/|\/embed\/)/);
      return result[2] !== undefined ? result[2].split(/[^0-9a-z_\-]/i)[0] : false;
    },
  },
};
</script>
<style>
.project-costs-table {
  width: 100%;
  border-collapse: collapse;
  margin: 8px 0;
  font-size: 0.8em;
  text-align: left;
}

.project-costs-table th,
.project-costs-table td {
  border: 1px solid #ddd;
  padding: 4px;
}

.project-costs-table th {
  background-color: #f4f4f4;
  font-weight: bold;
  color: #333;
}

.project-costs-table td {
  color: #555;
}

.project-costs-table tr:nth-child(even) {
  background-color: #f9f9f9;
}

.project-costs-table td:first-child {
  font-weight: bold;
}

.project-costs-table th,
.project-costs-table td {
  text-align: center;
}

.project-costs-table td:nth-child(1) {
  text-align: left;
}

.project-costs-table td:nth-child(2) {
  text-align: right;
}

.project-costs-table td:nth-child(3) {
  text-align: right;
}

.project-costs-table tbody tr:last-child td:last-child {
  color: green;
  font-weight: bold;
  text-decoration: underline;
  text-align: right;
}

.embed-tags {
  display: flex;
  flex-wrap: wrap;
  margin-bottom: 2em;

  .tag {
    background: white;
    color: black;
    border-radius: 25px;
    padding: 0.5em 1em;
    display: inline-block;
    text-decoration: none;
    margin-right: 8px;
    margin-bottom: 8px;
    line-height: 100%;
    border: solid 2px #5077b2;
    text-transform: uppercase;
    font-size: 0.7em;
  }
}

.austria-tag {
  background: linear-gradient(
    0deg,
    rgba(255, 0, 0, 0.5) 25%,
    white 33%,
    white 66%,
    rgba(255, 0, 0, 0.5) 75%
  ) !important;
  border: 1px solid black !important;
  color: black !important;
  text-align: center;
  font-weight: bold;
}

ul.synergy-list {
  margin-top: 1em;
}

.nzl-title {
  font-weight: 600 !important;
  color: black !important;
  text-decoration: underline 3px #5077b2 !important;
  text-underline-offset: 5px !important;
  line-height: 1.5em;
}
</style>
