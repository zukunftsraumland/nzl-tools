<template>
  <div class="project-component-form-row">
    <div class="project-component-form-header">
      <h3 v-if="project.id">Projekt bearbeiten</h3>
      <h3 v-if="!project.id">Projekt erfassen</h3>
      <div class="project-component-form-header-actions">
        <a
          class="button warning"
          @click="project.isPublic = true"
          v-if="!project.isPublic"
          >Entwurf</a
        >
        <a
          class="button success"
          @click="project.isPublic = false"
          v-if="project.isPublic"
          >Öffentlich</a
        >
        <!-- <a class="button" :class="{'primary' : locale === 'de'}" @click="clickLocale('de')">DE</a>
        <a class="button" :class="{'primary' : locale === 'fr'}" @click="clickLocale('fr')">FR</a>
        <a class="button" :class="{'primary' : locale === 'it'}" @click="clickLocale('it')">IT</a> -->
        <a class="button" @click="showPreviewModal()"
          ><span class="material-icons">visibility</span></a
        >
        <a
          class="button error"
          @click="clickDeleteProject()"
          v-if="project.id"
          title="Löschen"
          ><span class="material-icons">delete</span></a
        >
        <a class="button warning" @click="$router.back()" title="Abbrechen"
          ><span class="material-icons">close</span></a
        >
        <a class="button primary" @click="clickSaveProject()" title="Speichern"
          >Speichern</a
        >
      </div>
    </div>
    <div class="project-component-form-header">
      <h3
        v-if="
          diff &&
          (selectedInboxItem.internalId ||
            selectedInboxItem.source !== 'regiosuisse')
        "
      >
        Externe Projektdaten
      </h3>
      <h3 v-if="diff === false" class="error">
        Projekt gelöscht oder nicht mehr verfügbar
      </h3>
      <div class="project-component-form-header-actions">
        <a class="button error" @click="clickDismissDiff()" v-if="diff"
          >Update verwerfen</a
        >
        <a
          class="button primary"
          @click="mergeAll(locale)"
          v-if="
            diff &&
            (selectedInboxItem.internalId ||
              selectedInboxItem.source !== 'regiosuisse')
          "
          >Alle Daten übernehmen</a
        >
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    project: Object,
    diff: [Object, Boolean],
    selectedInboxItem: Object,
    locale: String,
    showPreviewModal: Function,
    clickDeleteProject: Function,
    clickSaveProject: Function,
    clickDismissDiff: Function,
    clockLocale: Function,
    mergeAll: Function,
  },
};
</script>
