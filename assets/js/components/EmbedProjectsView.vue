<template>
  <div class="embed-projects-view" :class="$env.INSTANCE_ID + '-projects-view'">
    <div class="embed-projects-view-header">
      <h1 class="embed-projects-view-header-title">
        {{ translateField(project, "title", locale) }}
      </h1>

      <a class="embed-projects-view-header-close" @click="clickClose()"></a>
    </div>

    <div class="embed-projects-view-content">
      <div class="embed-projects-view-content-text" v-html="translateField(project, 'description', locale)"></div>

      <div v-if="project.exemplary">
        <h4>Was macht dieses Projekt besonders nachahmenswert?</h4>
        <div class="embed-projects-view-content-text" v-html="project.exemplary"></div>
      </div>

      <div v-if="project.initialContext">
        <h4>Kontext</h4>
        <div class="embed-projects-view-content-text" v-html="project.initialContext"></div>
      </div>

      <div v-if="project.initialContextGoals">
        <h4>Ziele</h4>
        <div class="embed-projects-view-content-text" v-html="project.initialContextGoals"></div>
      </div>

      <div v-if="project.fundingMethod">
        <h4>Maßnahmen im Projekt</h4>
        <div class="embed-projects-view-content-text" v-html="project.fundingMethod"></div>
      </div>

      <div v-if="project.fundingMethodStakeholders">
        <h4>Welche Stakeholder waren entscheidend?</h4>
        <div class="embed-projects-view-content-text" v-html="project.fundingMethodStakeholders"></div>
      </div>

      <div v-if="project.resultsQuantity">
        <h4>Ergebnisse und Wirkungen (Quantitativ)</h4>
        <div class="embed-projects-view-content-text" v-html="project.resultsQuantity"></div>
      </div>

      <div v-if="project.resultsQuality">
        <h4>Ergebnisse und Wirkungen (Qualitativ)</h4>
        <div class="embed-projects-view-content-text" v-html="project.resultsQuality"></div>
      </div>

      <div v-if="project.additionalValue">
        <h4>Mehrwert durch Vernetzung</h4>
        <div class="embed-projects-view-content-text" v-html="project.additionalValue"></div>
      </div>

      <div v-if="project.additionalValueResult">
        <h4>Mehrwert durch Vernetzung Ergebnisse</h4>
        <div class="embed-projects-view-content-text" v-html="project.additionalValueResult"></div>
      </div>

      <div v-if="project.innovations">
        <h4>Innovation</h4>
        <div class="embed-projects-view-content-text" v-html="project.innovations"></div>
      </div>

      <div v-if="project.integrationYoungCitizen">
        <h4>Einbeziehung junger Menschen</h4>
        <div class="embed-projects-view-content-text" v-html="project.integrationYoungCitizen"></div>
      </div>

      <div v-if="project.integrationFemaleCitizen">
        <h4>Einbeziehung von Frauen</h4>
        <div class="embed-projects-view-content-text" v-html="project.integrationFemaleCitizen"></div>
      </div>

      <div v-if="project.integrationMinorities">
        <h4>Inklusion</h4>
        <div class="embed-projects-view-content-text" v-html="project.integrationMinorities"></div>
      </div>

      <div v-if="project.learningExperience">
        <h4>Die wichtigsten Lernerfahrungen</h4>
        <div class="embed-projects-view-content-text" v-html="project.learningExperience"></div>
      </div>

      <div v-if="project.synergyFundTags.length > 0">
        <h4>Synergien mit anderen EU-Politiken (GAP und andere EU Förderquellen)</h4>
        <ul>
          <li v-for="tag in project.synergyFundTags">
            <span>{{ tag.name }}</span>
          </li>
        </ul>
      </div>

      <div v-if="project.synergyGoalTags.length > 0">
        <h4>Dieses Projekt trägt zu Zielen folgenden europäischen und internationalen Politiken bei:</h4>
        <ul>
          <li v-for="tag in project.synergyGoalTags">
            <span>{{ tag.name }}</span>
          </li>
        </ul>
      </div>

      <div v-if="project.transferable">
        <h4>Übertragbarkeit</h4>
        <div class="embed-projects-view-content-text" v-html="project.transferable"></div>
      </div>

      <div v-if="project.transferDetails">
        <h4>Dieses Projekt wurde kopiert von:</h4>
        <div class="embed-projects-view-content-text" v-html="project.transferDetails"></div>
      </div>


      <div class="embed-projects-view-content-downloads" v-if="translateField(project, 'files', locale)?.length">
        <h4>{{ $t("Downloads", locale) }}</h4>
        <div class="embed-projects-view-content-downloads-download"
          v-for="(file, index) in translateField(project, 'files', locale)">
          <a :href="$env.HOST + '/api/v1/files/download/' + file.id + '.' + file.extension" download>
            {{ file.description || "Datei " + (index + 1) }}
          </a>
        </div>
      </div>

      <div class="embed-projects-view-content-contacts" v-if="translateField(project, 'contacts', locale)?.length">
        <h4>{{ $t("Kontakt", locale) }}</h4>
        <div class="embed-projects-view-content-contacts-contact"
          v-for="contact in translateField(project, 'contacts', locale)">
          <p>
            <template v-if="contact.name">
              <strong>{{ contact.name }}</strong><br />
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
              <a :href="'tel:' + contact.phone">{{ contact.phone }}</a><br />
            </template>
            <template v-if="contact.email">
              <a :href="'mailto:' + contact.email">{{ contact.email }}</a><br />
            </template>
            <template v-if="contact.website && contact.website.startsWith('http')">
              <a :href="contact.website" target="_blank">{{
                contact.website.split("://", 2)[1]
              }}</a><br />
            </template>
            <template v-if="contact.website && !contact.website.startsWith('http')">
              <a :href="'http://' + contact.website" target="_blank">{{
                contact.website
              }}</a><br />
            </template>
          </p>
        </div>
      </div>

      <div class="embed-projects-view-content-gallery"
        v-if="(translateField(project, 'images', locale) || []).length > 1">
        <div class="embed-projects-view-content-gallery-image"
          v-for="image in (translateField(project, 'images', locale) || []).slice(1)">
          <a @click="clickShowImage(image)">
            <img :src="$env.HOST + '/api/v1/files/view/' + image.id + '.' + image.extension" alt="" />
          </a>
        </div>
      </div>

      <div class="embed-projects-view-content-videos"
        v-if="(translateField(project, 'videos', locale) || []).length > 1">
        <div class="embed-projects-view-content-videos-video"
          v-for="video in translateField(project, 'videos', locale) || []">
          <div class="youtube-embed" v-if="parseYoutubeId(video.url)">
            <iframe width="560" height="315"
              :src="'https://www.youtube-nocookie.com/embed/' + parseYoutubeId(video.url)" frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen></iframe>
          </div>
        </div>
      </div>

      <div v-if="templateHook('projectContentAfter', project)" v-html="templateHook('projectContentAfter', project)">
      </div>
    </div>

    <div class="embed-projects-view-sidebar">
      <div v-if="templateHook('projectSidebarImage', project)" v-html="templateHook('projectSidebarImage', project)">
      </div>

      <template v-else>
        <div class="embed-projects-view-sidebar-image"
          v-for="image in (translateField(project, 'images', locale) || []).slice(0, 1)">
          <a @click="clickShowImage(image)">
            <img :src="$env.HOST + '/api/v1/files/view/' + image.id + '.' + image.extension" alt="" />
          </a>
        </div>
      </template>

      <template v-if="statesHTML">
        <h3>{{ $t("Kanton", locale) }}</h3>
        <p v-html="statesHTML"></p>
      </template>

      <template v-if="topicsHTML">
        <h3>{{ $t("Schwerpunkte", locale) }}</h3>
        <p v-html="topicsHTML"></p>
      </template>
      <p class="embed-projects-view-content-tags" v-if="translateField(project, 'tags', locale)?.length">
      <h3>{{ $t("Schlagworte", locale) }}</h3>

      <div class="embed-projects-view-content-tags-tag" v-for="tag in project.tags.filter((tag) => tag.context === 'tag')">
        <span>{{ tag.name }}</span>
      </div>
      </p>
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
      </template>

      <template v-for="financing in project.financing">
        <h3 v-if="financing.id === 'costsFederation'">
          {{ $t("GAP Strategieplan", locale) }}
        </h3>
        <h3 v-if="financing.id === 'costsCanton'">
          {{ $t("Private und Eigenmittel", locale) }}
        </h3>
        <h3 v-if="financing.id === 'costsExternal'">
          {{ $t("Andere Finanzquellen", locale) }}
        </h3>
        <p>{{ $helpers.formatPercent(financing.value, project.projectCosts) }}</p>
      </template>

      <template v-if="linksHTML">
        <h3>{{ $t("Links", locale) }}</h3>
        <p v-html="linksHTML"></p>
      </template>

      <div v-if="templateHook('projectSidebarAfter', project)" v-html="templateHook('projectSidebarAfter', project)">
      </div>
    </div>

    <transition name="embed-projects-view-lightbox" mode="out-in">
      <div class="embed-projects-view-lightbox" v-if="lightboxImage" @click="clickHideImage()">
        <div class="embed-projects-view-lightbox-content" :style="{
          backgroundImage:
            'url(' +
            $env.HOST +
            '/api/v1/files/view/' +
            lightboxImage.id +
            '.' +
            lightboxImage.extension +
            ')',
        }"></div>

        <div class="embed-projects-view-lightbox-description" @click.stop
          v-if="lightboxImage.description || lightboxImage.copyright">
          <template v-if="lightboxImage.description">{{
            lightboxImage.description
          }}</template>
          <template v-if="lightboxImage.description && lightboxImage.copyright">
            |
          </template>
          <template v-if="lightboxImage.copyright">© {{ lightboxImage.copyright }}</template>
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

  computed: {
    ...mapState({
      states: (state) => state.states.all,
      topics: (state) => state.topics.all,
      programs: (state) => state.programs.all,
      instruments: (state) => state.instruments.all,
    }),
    ...mapGetters({
      getStateById: "states/getById",
      getTopicById: "topics/getById",
      getProgramById: "programs/getById",
      getInstrumentById: "instruments/getById",
    }),
    statesHTML() {
      let result = [];

      this.project.states.forEach((item) => {
        let row = this.translateField(this.getStateById(item.id), "name", this.locale);

        result.push(row);
      });

      return result.join(", ");
    },
    topicsHTML() {
      let result = [];

      this.project.topics.forEach((item) => {
        let row = this.translateField(this.getTopicById(item.id), "name", this.locale);

        result.push(row);
      });

      return result.join(", ");
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

      return result.join(", ");
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
      const clientOptions = this.$clientOptions || {}; // Provide a fallback
      const templateHooks = clientOptions.templateHooks || {}; // Provide a fallback for templateHooks

      if (templateHooks[name]) {
        return templateHooks[name](this, ...params);
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
