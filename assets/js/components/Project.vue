<template>
  <div class="project-component">
    <div
      class="loading-overlay"
      :class="{
        visible: isLoading('projects/*') || isLoading('inbox/*'),
      }"
    />
    <div class="project-component-form" v-if="project">
      <ProjectHeader
        :project="project"
        :diff="diff"
        :selectedInboxItem="selectedInboxItem"
        :locale="locale"
        :showPreviewModal="showPreviewModal"
        :clickDeleteProject="clickDeleteProject"
        :clickSaveProject="clickSaveProject"
        :clickDismissDiff="clickDismissDiff"
        :clickLocale="clickLocale"
        :mergeAll="mergeAll"
      />
      <FieldWrapper
        :fields="[
          {
            name: 'title',
            label: 'Projektname',
            type: 'text',
            columnsize: 7,
          },
          {
            name: 'projectCode',
            label: 'Projektcode',
            type: 'text',
            columnsize: 3,
          },
          {
            name: 'caseStudy',
            label: 'Case Study',
            type: 'checkbox',
            columnsize: 2,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />
      <FieldWrapper
        :fields="[
          {
            name: 'description',
            label: 'Beschreibung',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.description,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />
      <FieldWrapper
        :fields="[
          {
            label: 'Fördermaßnahme',
            name: 'fundingStructure',
            type: 'period-select',
            columnsize: 12,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFundingStructureFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        :fields="[
          {
            name: 'localWorkgroup',
            label: 'Lokale Arbeitsgruppe (LAG)',
            type: 'select',
            options: localWorkgroups,
            columnsize: 6,
          },
          {
            name: 'cooperationProjectAt',
            label: 'mit LAGs aus AT',
            type: 'checkbox',
            columnsize: 3,
          },
          {
            name: 'cooperationProjectEu',
            label: 'mit LAGs aus EU',
            type: 'checkbox',
            columnsize: 3,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.cooperationProjectAt"
        :fields="[
          {
            name: 'localWorkgroups',
            label: 'Kooperation mit LAG:',
            type: 'tag-select',
            options: localWorkgroups,
            columnsize: 6,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />
      <FieldWrapper
        v-if="$env.PROJECTS_ENABLE_STATES"
        :fields="[
          {
            name: 'states',
            label: 'Projektregion',
            type: 'tag-select',
            options: states,
            columnsize: 12,
            selectAllValue: 'Österreichweit',
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="$env.PROJECTS_ENABLE_PROGRAMS || $env.PROJECTS_ENABLE_INSTRUMENTS"
        :fields="[
          $env.PROJECTS_ENABLE_PROGRAMS && {
            name: 'programs',
            label: 'Programme',
            type: 'tag-select',
            options: programs,
            columnsize: 6,
          },
          $env.PROJECTS_ENABLE_INSTRUMENTS && {
            name: 'instruments',
            label: 'Finanzierung',
            type: 'tag-select',
            options: instruments,
            columnsize: 6,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="$env.PROJECTS_ENABLE_GEOGRAPHIC_REGIONS"
        :fields="[
          {
            name: 'geographicRegions',
            label: 'Geographische Regionen',
            type: 'tag-select',
            options: geographicRegions,
            columnsize: 12,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="$env.PROJECTS_ENABLE_TOPICS"
        :fields="[
          {
            name: 'topics',
            label: 'Schwerpunkte',
            type: 'tag-select',
            options: topics,
            columnsize: 6,
          },
          {
            name: 'tags',
            label: 'Individuelle Schlagworte',
            type: 'tag-search-select',
            context: 'tag',
            options: tags.filter((tag) => tag.context === 'tag'),
            columnsize: 6,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="$env.PROJECTS_ENABLE_BUSINESS_SECTORS"
        :fields="[
          {
            name: 'businessSectors',
            label: 'Geschäftsfelder',
            type: 'tag-select',
            options: businessSectors.filter(
              (businessSector) =>
                !businessSector.context || businessSector.context === 'project'
            ),
            columnsize: 6,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'exemplary',
            label: 'Was macht dieses Projekt besonders nachahmenswert?',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.exemplary,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'initialContext',
            label: 'Kontext',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.initialContext,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'initialContextGoals',
            label: 'Ziele',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.initialContextGoals,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'fundingMethod',
            label: 'Maßnahmen im Projekt',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.fundingMethod,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'fundingMethodStakeholders',
            label: 'Welche Stakeholder waren entscheidend?',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.fundingMethodStakeholders,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'resultsQuantity',
            label: 'Ergebnisse und Wirkungen (Quantitativ)',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.resultsQuantity,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'resultsQuality',
            label: 'Ergebnisse und Wirkungen (Qualitativ)',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.resultsQuality,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'additionalValue',
            label: 'Mehrwert durch Vernetzung',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.additionalValue,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'additionalValueResult',
            label: 'Möglichkeiten zur Vernetzung',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.additionalValueResult,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'innovations',
            label: 'Innovation',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.innovations,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'integrationYoungCitizen',
            label: 'Einbeziehung junger Menschen',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.integrationYoungCitizen,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'integrationFemaleCitizen',
            label: 'Einbeziehung von Frauen',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.integrationFemaleCitizen,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'integrationMinorities',
            label: 'Inklusion',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.integrationMinorities,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'learningExperience',
            label: 'Die wichtigsten Lernerfahrungen',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.learningExperience,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'transferable',
            label: 'Übertragbarkeit',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.transferable,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'transferDetails',
            label: 'Details zur Übertragung dieses Projekts',
            type: 'ckeditor',
            editor: editor,
            editorConfig: editorConfig,
            columnsize: 12,
            tooltip: tooltips.transferDetails,
            isAccordion: true,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />
      <div
        class="project-component-form-row"
        v-if="$env.PROJECTS_ENABLE_PROJECT_COSTS || $env.PROJECTS_ENABLE_FINANCING"
      >
        <div class="project-component-form-section">
          <div class="row">
            <div class="col-md-4" v-if="$env.PROJECTS_ENABLE_PROJECT_COSTS">
              <label for="projectCosts">Gesamtprojektkosten (€)</label>
              <input
                id="projectCosts"
                type="text"
                class="form-control"
                :value="project.projectCosts"
                @change="
                  project.projectCosts = $event.target.value = filterNumber(
                    $event.target.value
                  )
                "
              />
            </div>
            <div class="col-md-8" v-if="$env.PROJECTS_ENABLE_FINANCING">
              <div class="row" v-for="(financing, index) in project.financing">
                <div class="col-md-5">
                  <label v-if="index === 0">Weitere Projektkosten (%)</label>
                  <div class="select-wrapper">
                    <select class="form-control" v-model="financing.id">
                      <option value="costsFederation">GAP Strategieplan</option>
                      <option value="costsCanton">Private und Eigenmittel</option>
                      <option value="costsExternal">Andere Finanzquellen</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-5">
                  <label v-if="index === 0">Anteil in Prozent (%)</label>
                  <input
                    placeholder="Wert"
                    type="text"
                    class="form-control"
                    :value="financing.value"
                    @change="
                      financing.value = $event.target.value = filterNumber(
                        $event.target.value
                      )
                    "
                  />
                </div>
                <div class="col-md-2">
                  <label v-if="index === 0">Verwerfen</label>
                  <a
                    class="button warning"
                    @click="
                      project.financing.splice(project.financing.indexOf(financing), 1)
                    "
                  >
                    <span class="material-icons">cancel</span>
                  </a>
                </div>
              </div>
              <a
                class="form-control-add"
                @click="project.financing.push({ id: '', value: 0 })"
              >
                <span class="material-icons">add</span> Kostenstelle hinzufügen
              </a>
            </div>
          </div>
        </div>

        <div class="project-component-form-section">
          <div
            class="row"
            v-if="
              diff &&
              (selectedInboxItem.internalId || selectedInboxItem.source !== 'regiosuisse')
            "
          >
            <div
              class="col-md-4"
              v-if="$env.PROJECTS_ENABLE_PROJECT_COSTS"
              :class="{ disabled: project.projectCosts == diff.projectCosts }"
            >
              <label for="projectCostsDiff">
                <span
                  class="material-icons"
                  v-if="project.projectCosts != diff.projectCosts"
                  @click="mergeFields('projectCosts')"
                  >keyboard_backspace</span
                >
                Projektkosten
              </label>
              <input
                readonly
                id="projectCostsDiff"
                type="text"
                class="form-control"
                v-model="diff.projectCosts"
              />
            </div>
            <div
              class="col-md-8"
              v-if="$env.PROJECTS_ENABLE_FINANCING"
              :class="{
                disabled: compareOptions(project.financing, diff.financing),
              }"
            >
              <label>
                <span
                  class="material-icons"
                  v-if="!compareOptions(project.financing, diff.financing)"
                  @click="mergeOptions('financing')"
                  >keyboard_backspace</span
                >
                Weitere Projektkosten (%)
              </label>
              <div class="row" v-for="financing in diff.financing">
                <div class="col-md-6">
                  <div class="select-wrapper">
                    <select class="form-control" v-model="financing.id" disabled>
                      <option value="costsFederation">GAP Strategieplan</option>
                      <option value="costsCanton">Private und Eigenmittel</option>
                      <option value="costsExternal">Andere Finanzquellen</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <input
                    readonly
                    placeholder="Wert"
                    type="text"
                    class="form-control"
                    v-model="financing.value"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <FieldWrapper
        v-if="project.caseStudy"
        :fields="[
          {
            name: 'synergyFundTags',
            label: 'Synergien mit EU Politiken',
            type: 'tag-search-select',
            context: 'synergyFundTag',
            options: tags.filter((tag) => tag.context === 'synergyFundTag'),
            columnsize: 6,
          },
          {
            name: 'synergyGoalTags',
            label: 'Ziel Trägt zu EU Politiken bei',
            type: 'tag-search-select',
            context: 'synergyGoalTag',
            options: tags.filter((tag) => tag.context === 'synergyGoalTag'),
            columnsize: 6,
          },
        ]"
        :project="project"
        :diff="diff"
        :locale="locale"
        @mergeFields="mergeFields"
        @update:project="project = $event"
      />

      <div
        class="project-component-form-row"
        v-if="$env.PROJECTS_ENABLE_START_DATE || $env.PROJECTS_ENABLE_END_DATE"
      >
        <div class="project-component-form-section">
          <div class="row">
            <div class="col-md-6" v-if="$env.PROJECTS_ENABLE_START_DATE">
              <label for="startDate">Start-Datum</label>
              <date-picker mode="date" v-model="project.startDate" :locale="'de'">
                <template v-slot="{ inputValue, inputEvents }">
                  <input
                    type="text"
                    class="form-control"
                    :value="inputValue"
                    v-on="inputEvents"
                    id="startDate"
                  />
                </template>
              </date-picker>
            </div>
            <div class="col-md-6" v-if="$env.PROJECTS_ENABLE_END_DATE">
              <label for="endDate">End-Datum</label>
              <date-picker mode="date" v-model="project.endDate" :locale="'de'">
                <template v-slot="{ inputValue, inputEvents }">
                  <input
                    type="text"
                    class="form-control"
                    :value="inputValue"
                    v-on="inputEvents"
                    id="endDate"
                  />
                </template>
              </date-picker>
            </div>
          </div>
        </div>

        <div class="project-component-form-section">
          <div
            class="row"
            v-if="
              diff &&
              (selectedInboxItem.internalId || selectedInboxItem.source !== 'regiosuisse')
            "
          >
            <div
              class="col-md-6"
              v-if="$env.PROJECTS_ENABLE_START_DATE"
              :class="{
                disabled: compareDates(project.startDate, diff.startDate),
              }"
            >
              <label for="startDateDiff">
                <span
                  class="material-icons"
                  v-if="!compareDates(project.startDate, diff.startDate)"
                  @click="mergeFields('startDate')"
                  >keyboard_backspace</span
                >
                Start-Datum
              </label>
              <input
                readonly
                id="startDateDiff"
                type="text"
                class="form-control"
                :value="formatDate(diff.startDate)"
              />
            </div>
            <div
              class="col-md-6"
              v-if="$env.PROJECTS_ENABLE_END_DATE"
              :class="{
                disabled: compareDates(project.endDate, diff.endDate),
              }"
            >
              <label for="endDateDiff">
                <span
                  class="material-icons"
                  v-if="!compareDates(project.endDate, diff.endDate)"
                  @click="mergeFields('endDate')"
                  >keyboard_backspace</span
                >
                End-Datum
              </label>
              <input
                readonly
                id="endDateDiff"
                type="text"
                class="form-control"
                :value="formatDate(diff.endDate)"
              />
            </div>
          </div>
        </div>
      </div>

      <template v-if="$env.PROJECTS_ENABLE_LINKS">
        <div class="project-component-form-row" v-if="!isTranslationModeEnabled()">
          <div class="project-component-form-section">
            <div class="row">
              <div class="col-md-12">
                <label>Links</label>
                <div class="row" v-for="link in project.links">
                  <div class="col-md-5">
                    <input
                      placeholder="Bezeichnung"
                      type="text"
                      class="form-control"
                      v-model="link.label"
                    />
                  </div>
                  <div class="col-md-5">
                    <input
                      placeholder="URL"
                      type="text"
                      class="form-control"
                      v-model="link.url"
                    />
                  </div>
                  <div class="col-md-2">
                    <a
                      class="button warning"
                      @click="project.links.splice(project.links.indexOf(link), 1)"
                    >
                      <span class="material-icons">cancel</span>
                    </a>
                  </div>
                </div>
                <a
                  class="form-control-add"
                  @click="project.links.push({ label: '', value: '' })"
                >
                  <span class="material-icons">add</span> Link hinzufügen
                </a>
              </div>
            </div>
          </div>

          <div class="project-component-form-section">
            <div
              class="row"
              v-if="
                diff &&
                (selectedInboxItem.internalId ||
                  selectedInboxItem.source !== 'regiosuisse')
              "
            >
              <div
                class="col-md-12"
                :class="{
                  disabled: compareOptions(project.links, diff.links),
                }"
              >
                <label>
                  <span
                    class="material-icons"
                    v-if="!compareOptions(project.links, diff.links)"
                    @click="mergeOptions('links')"
                    >keyboard_backspace</span
                  >
                  Links
                </label>
                <div class="row" v-for="link in diff.links">
                  <div class="col-md-6">
                    <input
                      readonly
                      placeholder="Bezeichnung"
                      type="text"
                      class="form-control"
                      v-model="link.label"
                    />
                  </div>
                  <div class="col-md-6">
                    <input
                      readonly
                      placeholder="URL"
                      type="text"
                      class="form-control"
                      v-model="link.url"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="project-component-form-row" v-if="isTranslationModeEnabled()">
          <div class="project-component-form-section">
            <div class="row">
              <div class="col-md-12">
                <label>Links (Übersetzung {{ locale.toUpperCase() }})</label>
                <div class="row" v-for="link in project.translations[locale].links">
                  <div class="col-md-5">
                    <input
                      placeholder="Bezeichnung"
                      type="text"
                      class="form-control"
                      v-model="link.label"
                    />
                  </div>
                  <div class="col-md-5">
                    <input
                      placeholder="URL"
                      type="text"
                      class="form-control"
                      v-model="link.url"
                    />
                  </div>
                  <div class="col-md-2">
                    <a
                      class="button warning"
                      @click="
                        project.translations[locale].links.splice(
                          project.translations[locale].links.indexOf(link),
                          1
                        )
                      "
                    >
                      <span class="material-icons">cancel</span>
                    </a>
                  </div>
                </div>
                <a
                  class="form-control-add"
                  @click="
                    project.translations[locale].links.push({
                      label: '',
                      value: '',
                    })
                  "
                >
                  <span class="material-icons">add</span> Link hinzufügen
                </a>
              </div>
            </div>
          </div>

          <div
            class="project-component-form-section"
            v-if="diff && diff.translations[locale] && diff.translations[locale].links"
          >
            <div
              class="row"
              v-if="
                diff &&
                (selectedInboxItem.internalId ||
                  selectedInboxItem.source !== 'regiosuisse')
              "
            >
              <div
                class="col-md-12"
                :class="{
                  disabled: compareOptions(
                    project.translations[locale].links,
                    diff.translations[locale].links
                  ),
                }"
              >
                <label>
                  <span
                    class="material-icons"
                    v-if="
                      !compareOptions(
                        project.translations[locale].links,
                        diff.translations[locale].links
                      )
                    "
                    @click="mergeOptions('links', locale)"
                    >keyboard_backspace</span
                  >
                  Links
                </label>
                <div class="row" v-for="link in diff.translations[locale].links">
                  <div class="col-md-6">
                    <input
                      readonly
                      placeholder="Bezeichnung"
                      type="text"
                      class="form-control"
                      v-model="link.label"
                    />
                  </div>
                  <div class="col-md-6">
                    <input
                      readonly
                      placeholder="URL"
                      type="text"
                      class="form-control"
                      v-model="link.url"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="project-component-form-section" v-else>
            <div
              class="row"
              v-if="
                diff &&
                (selectedInboxItem.internalId ||
                  selectedInboxItem.source !== 'regiosuisse')
              "
            >
              <div
                class="col-md-12"
                :class="{
                  disabled: compareOptions(
                    project.translations[locale].links,
                    diff.links
                  ),
                }"
              >
                <label>
                  <span
                    class="material-icons"
                    v-if="!compareOptions(project.translations[locale].links, diff.links)"
                    @click="mergeOptions('links', locale)"
                    >keyboard_backspace</span
                  >
                  Links
                </label>
                <div class="row" v-for="link in project.links">
                  <div class="col-md-6">
                    <input
                      readonly
                      placeholder="Bezeichnung"
                      type="text"
                      class="form-control"
                      v-model="link.label"
                    />
                  </div>
                  <div class="col-md-6">
                    <input
                      readonly
                      placeholder="URL"
                      type="text"
                      class="form-control"
                      v-model="link.url"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template v-if="$env.PROJECTS_ENABLE_IMAGES">
        <div class="project-component-form-row">
          <div class="project-component-form-section">
            <div class="row">
              <div class="col-md-12">
                <label for="images">Bilder</label>
                <image-selector
                  id="images"
                  :items="project.images"
                  :locale="locale"
                  @changed="updateImages"
                ></image-selector>
              </div>
            </div>
          </div>

          <div class="project-component-form-section">
            <div
              class="row"
              v-if="
                diff &&
                (selectedInboxItem.internalId ||
                  selectedInboxItem.source !== 'regiosuisse')
              "
            >
              <div
                class="col-md-12"
                :class="{
                  disabled: compareOptions(project.images, diff.images),
                }"
              >
                <label for="images">
                  <span
                    class="material-icons"
                    v-if="!compareOptions(project.images, diff.images)"
                    @click="mergeOptions('images')"
                    >keyboard_backspace</span
                  >
                  Bilder
                </label>
                <image-selector
                  id="images"
                  :items="diff.images"
                  :locale="locale"
                  :readonly="true"
                ></image-selector>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template v-if="$env.PROJECTS_ENABLE_FILES">
        <div class="project-component-form-row" v-if="!isTranslationModeEnabled()">
          <div class="project-component-form-section">
            <div class="row">
              <div class="col-md-12">
                <label for="files">Dokumente</label>
                <file-selector
                  id="files"
                  :items="project.files"
                  @changed="updateFiles"
                ></file-selector>
              </div>
            </div>
          </div>

          <div class="project-component-form-section">
            <div
              class="row"
              v-if="
                diff &&
                (selectedInboxItem.internalId ||
                  selectedInboxItem.source !== 'regiosuisse')
              "
            >
              <div
                class="col-md-12"
                :class="{
                  disabled: compareOptions(project.files, diff.files),
                }"
              >
                <label for="files">
                  <span
                    class="material-icons"
                    v-if="!compareOptions(project.files, diff.files)"
                    @click="mergeOptions('files')"
                    >keyboard_backspace</span
                  >
                  Dokumente
                </label>
                <file-selector
                  id="files"
                  :items="diff.files"
                  :readonly="true"
                ></file-selector>
              </div>
            </div>
          </div>
        </div>

        <div class="project-component-form-row" v-if="isTranslationModeEnabled()">
          <div class="project-component-form-section">
            <div class="row">
              <div class="col-md-12">
                <label for="files"
                  >Dokumente (Übersetzung {{ locale.toUpperCase() }})</label
                >
                <file-selector
                  id="files"
                  :items="project.translations[locale].files"
                  @changed="updateTranslatedFiles"
                ></file-selector>
              </div>
            </div>
          </div>

          <div class="project-component-form-section">
            <div
              class="row"
              v-if="
                diff &&
                diff.translations[locale] &&
                (selectedInboxItem.internalId ||
                  selectedInboxItem.source !== 'regiosuisse')
              "
            >
              <div
                class="col-md-12"
                :class="{
                  disabled: compareOptions(
                    project.translations[locale].files,
                    diff.translations[locale].files
                  ),
                }"
              >
                <label for="files">
                  <span
                    class="material-icons"
                    v-if="
                      !compareOptions(
                        project.translations[locale].files,
                        diff.translations[locale].files
                      )
                    "
                    @click="mergeOptions('files', locale)"
                    >keyboard_backspace</span
                  >
                  Dokumente
                </label>
                <file-selector
                  id="files"
                  :items="diff.translations[locale].files"
                  :readonly="true"
                ></file-selector>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template v-if="$env.PROJECTS_ENABLE_VIDEOS">
        <div class="project-component-form-row" v-if="!isTranslationModeEnabled()">
          <div class="project-component-form-section">
            <div class="row">
              <div class="col-md-12">
                <label>Videos</label>
                <div class="row" v-for="video in project.videos">
                  <div class="col-md-5">
                    <input
                      placeholder="Bezeichnung"
                      type="text"
                      class="form-control"
                      v-model="video.label"
                    />
                  </div>
                  <div class="col-md-5">
                    <input
                      placeholder="URL"
                      type="text"
                      class="form-control"
                      v-model="video.url"
                    />
                  </div>
                  <div class="col-md-2">
                    <a
                      class="button warning"
                      @click="project.videos.splice(project.videos.indexOf(video), 1)"
                    >
                      <span class="material-icons">cancel</span>
                    </a>
                  </div>
                  <div class="col-md-10" v-if="parseYoutubeId(video.url)">
                    <div class="youtube-embed">
                      <iframe
                        width="560"
                        height="315"
                        :src="
                          'https://www.youtube-nocookie.com/embed/' +
                          parseYoutubeId(video.url)
                        "
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                      ></iframe>
                    </div>
                  </div>
                </div>
                <a
                  class="form-control-add"
                  @click="project.videos.push({ label: '', value: '' })"
                >
                  <span class="material-icons">add</span> Video hinzufügen
                </a>
              </div>
            </div>
          </div>

          <div class="project-component-form-section">
            <div
              class="row"
              v-if="
                diff &&
                (selectedInboxItem.internalId ||
                  selectedInboxItem.source !== 'regiosuisse')
              "
            >
              <div
                class="col-md-12"
                :class="{
                  disabled: compareOptions(project.videos, diff.videos),
                }"
              >
                <label>
                  <span
                    class="material-icons"
                    v-if="!compareOptions(project.videos, diff.videos)"
                    @click="mergeOptions('videos')"
                    >keyboard_backspace</span
                  >
                  Videos
                </label>
                <div class="row" v-for="video in diff.videos">
                  <div class="col-md-5">
                    <input
                      placeholder="Bezeichnung"
                      type="text"
                      class="form-control"
                      v-model="video.label"
                      readonly
                    />
                  </div>
                  <div class="col-md-5">
                    <input
                      placeholder="URL"
                      type="text"
                      class="form-control"
                      v-model="video.url"
                      readonly
                    />
                  </div>
                  <div class="col-md-10" v-if="parseYoutubeId(video.url)">
                    <div class="youtube-embed">
                      <iframe
                        width="560"
                        height="315"
                        :src="
                          'https://www.youtube-nocookie.com/embed/' +
                          parseYoutubeId(video.url)
                        "
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                      ></iframe>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="project-component-form-row" v-if="isTranslationModeEnabled()">
          <div class="project-component-form-section">
            <div class="row">
              <div class="col-md-12">
                <label>Videos (Übersetzung {{ locale.toUpperCase() }})</label>
                <div class="row" v-for="video in project.translations[locale].videos">
                  <div class="col-md-5">
                    <input
                      placeholder="Bezeichnung"
                      type="text"
                      class="form-control"
                      v-model="video.label"
                    />
                  </div>
                  <div class="col-md-5">
                    <input
                      placeholder="URL"
                      type="text"
                      class="form-control"
                      v-model="video.url"
                    />
                  </div>
                  <div class="col-md-2">
                    <a
                      class="button warning"
                      @click="
                        project.translations[locale].videos.splice(
                          project.translations[locale].videos.indexOf(video),
                          1
                        )
                      "
                    >
                      <span class="material-icons">cancel</span>
                    </a>
                  </div>
                  <div class="col-md-10" v-if="parseYoutubeId(video.url)">
                    <div class="youtube-embed">
                      <iframe
                        width="560"
                        height="315"
                        :src="
                          'https://www.youtube-nocookie.com/embed/' +
                          parseYoutubeId(video.url)
                        "
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                      ></iframe>
                    </div>
                  </div>
                </div>
                <a
                  class="form-control-add"
                  @click="
                    project.translations[locale].videos.push({
                      label: '',
                      value: '',
                    })
                  "
                >
                  <span class="material-icons">add</span> Video hinzufügen
                </a>
              </div>
            </div>
          </div>

          <div class="project-component-form-section">
            <div
              class="row"
              v-if="
                diff &&
                diff.translations[locale] &&
                (selectedInboxItem.internalId ||
                  selectedInboxItem.source !== 'regiosuisse')
              "
            >
              <div
                class="col-md-12"
                :class="{
                  disabled: compareOptions(
                    project.translations[locale].videos,
                    diff.translations[locale].videos
                  ),
                }"
              >
                <label>
                  <span
                    class="material-icons"
                    v-if="
                      !compareOptions(
                        project.translations[locale].videos,
                        diff.translations[locale].videos
                      )
                    "
                    @click="mergeOptions('videos', locale)"
                    >keyboard_backspace</span
                  >
                  Videos
                </label>
                <div class="row" v-for="video in diff.translations[locale].videos">
                  <div class="col-md-5">
                    <input
                      placeholder="Bezeichnung"
                      type="text"
                      class="form-control"
                      v-model="video.label"
                      readonly
                    />
                  </div>
                  <div class="col-md-5">
                    <input
                      placeholder="URL"
                      type="text"
                      class="form-control"
                      v-model="video.url"
                      readonly
                    />
                  </div>
                  <div class="col-md-10" v-if="parseYoutubeId(video.url)">
                    <div class="youtube-embed">
                      <iframe
                        width="560"
                        height="315"
                        :src="
                          'https://www.youtube-nocookie.com/embed/' +
                          parseYoutubeId(video.url)
                        "
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                      ></iframe>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <div class="project-component-form-row">
        <div class="project-component-form-section">
          <div class="row">
            <div class="col-md-6">
              <label for="lat">Breitengrad (Lat)</label>
              <input id="lat" type="text" class="form-control" v-model="project.lat" />
            </div>
            <div class="col-md-6">
              <label for="lng">Längengrad (Lng)</label>
              <input id="lng" type="text" class="form-control" v-model="project.lng" />
            </div>
          </div>
        </div>

        <div class="project-component-form-section">
          <div
            class="row"
            v-if="
              diff &&
              (selectedInboxItem.internalId || selectedInboxItem.source !== 'regiosuisse')
            "
          >
            <div class="col-md-6" :class="{ disabled: project.lat === diff.lat }">
              <label for="latDiff">
                <span
                  class="material-icons"
                  v-if="project.lat !== diff.lat"
                  @click="mergeFields('lat')"
                  >keyboard_backspace</span
                >
                Breitengrad (Lat)
              </label>
              <input
                readonly
                id="latDiff"
                type="text"
                class="form-control"
                v-model="diff.lat"
              />
            </div>
            <div class="col-md-6" :class="{ disabled: project.lng === diff.lng }">
              <label for="lngDiff">
                <span
                  class="material-icons"
                  v-if="project.lng !== diff.lng"
                  @click="mergeFields('lng')"
                  >keyboard_backspace</span
                >
                Längengrad (Lng)
              </label>
              <input
                readonly
                id="lngDiff"
                type="text"
                class="form-control"
                v-model="diff.lng"
              />
            </div>
          </div>
        </div>
      </div>

      <template v-if="$env.PROJECTS_ENABLE_CONTACTS">
        <div class="project-component-form-row" v-if="!isTranslationModeEnabled()">
          <div class="project-component-form-section">
            <div class="row">
              <div class="col-md-12">
                <label>Kontakte</label>
                <div
                  class="project-component-form-section-contact"
                  v-for="contact in project.contacts"
                >
                  <div class="row">
                    <div class="col-md-12">
                      <label>Name</label>
                      <input type="text" class="form-control" v-model="contact.name" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-2">
                      <label>Anrede</label>
                      <div class="select-wrapper">
                        <select class="form-control" v-model="contact.salutation">
                          <option value=""></option>
                          <option value="m">Herr</option>
                          <option value="f">Frau</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <label>Titel</label>
                      <input type="text" class="form-control" v-model="contact.title" />
                    </div>
                    <div class="col-md-4">
                      <label>Vorname</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="contact.firstName"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Nachname</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="contact.lastName"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <label>Funktion</label>
                      <input type="text" class="form-control" v-model="contact.role" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4">
                      <label>Telefon</label>
                      <input type="text" class="form-control" v-model="contact.phone" />
                    </div>
                    <div class="col-md-4">
                      <label>E-Mail</label>
                      <input type="text" class="form-control" v-model="contact.email" />
                    </div>
                    <div class="col-md-4">
                      <label>Website</label>
                      <input type="text" class="form-control" v-model="contact.website" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-5">
                      <label>Strasse</label>
                      <input type="text" class="form-control" v-model="contact.street" />
                    </div>
                    <div class="col-md-3">
                      <label>PLZ</label>
                      <input type="text" class="form-control" v-model="contact.zipCode" />
                    </div>
                    <div class="col-md-4">
                      <label>Ort</label>
                      <input type="text" class="form-control" v-model="contact.city" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <div
                        class="button warning"
                        @click="
                          project.contacts.splice(project.contacts.indexOf(contact), 1)
                        "
                      >
                        Kontakt entfernen
                      </div>
                    </div>
                  </div>
                </div>
                <div class="project-component-form-section-contact">
                  <div class="button primary" @click="project.contacts.push({})">
                    Kontakt hinzufügen
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="project-component-form-section">
            <div
              class="row"
              v-if="
                diff &&
                (selectedInboxItem.internalId ||
                  selectedInboxItem.source !== 'regiosuisse')
              "
            >
              <div
                class="col-md-12"
                :class="{
                  disabled: compareContacts(project.contacts, diff.contacts),
                }"
              >
                <label>
                  <span
                    class="material-icons"
                    v-if="!compareContacts(project.contacts, diff.contacts)"
                    @click="mergeOptions('contacts')"
                    >keyboard_backspace</span
                  >
                  Kontakte
                </label>
                <div
                  class="project-component-form-section-contact"
                  v-for="contact in diff.contacts"
                >
                  <div class="row">
                    <div class="col-md-12">
                      <label>Name</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.name"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-2">
                      <label>Anrede</label>
                      <div class="select-wrapper">
                        <select class="form-control" v-model="contact.salutation">
                          <option value=""></option>
                          <option value="m">Herr</option>
                          <option value="f">Frau</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <label>Titel</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.title"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Vorname</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.firstName"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Nachname</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.lastName"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <label>Funktion</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.role"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4">
                      <label>Telefon</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.phone"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>E-Mail</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.email"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Website</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.website"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-5">
                      <label>Strasse</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.street"
                      />
                    </div>
                    <div class="col-md-3">
                      <label>PLZ</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.zipCode"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Ort</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.city"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="project-component-form-row" v-if="isTranslationModeEnabled()">
          <div class="project-component-form-section">
            <div class="row">
              <div class="col-md-12">
                <label>Kontakte (Übersetzung {{ locale.toUpperCase() }})</label>
                <div
                  class="project-component-form-section-contact"
                  v-for="contact in project.translations[locale].contacts"
                >
                  <div class="row">
                    <div class="col-md-12">
                      <label>Name</label>
                      <input type="text" class="form-control" v-model="contact.name" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-2">
                      <label>Anrede</label>
                      <div class="select-wrapper">
                        <select class="form-control" v-model="contact.salutation">
                          <option value=""></option>
                          <option value="m">Herr</option>
                          <option value="f">Frau</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <label>Titel</label>
                      <input type="text" class="form-control" v-model="contact.title" />
                    </div>
                    <div class="col-md-4">
                      <label>Vorname</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="contact.firstName"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Nachname</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="contact.lastName"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <label>Funktion</label>
                      <input type="text" class="form-control" v-model="contact.role" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4">
                      <label>Telefon</label>
                      <input type="text" class="form-control" v-model="contact.phone" />
                    </div>
                    <div class="col-md-4">
                      <label>E-Mail</label>
                      <input type="text" class="form-control" v-model="contact.email" />
                    </div>
                    <div class="col-md-4">
                      <label>Website</label>
                      <input type="text" class="form-control" v-model="contact.website" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-5">
                      <label>Strasse</label>
                      <input type="text" class="form-control" v-model="contact.street" />
                    </div>
                    <div class="col-md-3">
                      <label>PLZ</label>
                      <input type="text" class="form-control" v-model="contact.zipCode" />
                    </div>
                    <div class="col-md-4">
                      <label>Ort</label>
                      <input type="text" class="form-control" v-model="contact.city" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <div
                        class="button warning"
                        @click="
                          project.translations[locale].contacts.splice(
                            project.translations[locale].contacts.indexOf(contact),
                            1
                          )
                        "
                      >
                        Kontakt entfernen
                      </div>
                    </div>
                  </div>
                </div>
                <div class="project-component-form-section-contact">
                  <div
                    class="button primary"
                    @click="project.translations[locale].contacts.push({})"
                  >
                    Kontakt hinzufügen
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div
            class="project-component-form-section"
            v-if="diff && diff.translations[locale] && diff.translations[locale].contacts"
          >
            <div
              class="row"
              v-if="
                diff &&
                (selectedInboxItem.internalId ||
                  selectedInboxItem.source !== 'regiosuisse')
              "
            >
              <div
                class="col-md-12"
                :class="{
                  disabled: compareContacts(
                    project.translations[locale].contacts,
                    diff.translations[locale].contacts
                  ),
                }"
              >
                <label>
                  <span
                    class="material-icons"
                    v-if="
                      !compareContacts(
                        project.translations[locale].contacts,
                        diff.translations[locale].contacts
                      )
                    "
                    @click="mergeOptions('contacts', locale)"
                    >keyboard_backspace</span
                  >
                  Kontakte
                </label>
                <div
                  class="project-component-form-section-contact"
                  v-for="contact in diff.translations[locale].contacts"
                >
                  <div class="row">
                    <div class="col-md-12">
                      <label>Name</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.name"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-2">
                      <label>Anrede</label>
                      <div class="select-wrapper">
                        <select class="form-control" v-model="contact.salutation">
                          <option value=""></option>
                          <option value="m">Herr</option>
                          <option value="f">Frau</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <label>Titel</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.title"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Vorname</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.firstName"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Nachname</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.lastName"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <label>Funktion</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.role"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4">
                      <label>Telefon</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.phone"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>E-Mail</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.email"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Website</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.website"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-5">
                      <label>Strasse</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.street"
                      />
                    </div>
                    <div class="col-md-3">
                      <label>PLZ</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.zipCode"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Ort</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.city"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="project-component-form-section" v-else>
            <div
              class="row"
              v-if="
                diff &&
                (selectedInboxItem.internalId ||
                  selectedInboxItem.source !== 'regiosuisse')
              "
            >
              <div
                class="col-md-12"
                :class="{
                  disabled: compareContacts(
                    project.translations[locale].contacts,
                    diff.contacts
                  ),
                }"
              >
                <label>
                  <span
                    class="material-icons"
                    v-if="
                      !compareContacts(
                        project.translations[locale].contacts,
                        diff.contacts
                      )
                    "
                    @click="mergeOptions('contacts', locale)"
                    >keyboard_backspace</span
                  >
                  Kontakte
                </label>
                <div
                  class="project-component-form-section-contact"
                  v-for="contact in diff.contacts"
                >
                  <div class="row">
                    <div class="col-md-12">
                      <label>Name</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.name"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-2">
                      <label>Anrede</label>
                      <div class="select-wrapper">
                        <select class="form-control" v-model="contact.salutation">
                          <option value=""></option>
                          <option value="m">Herr</option>
                          <option value="f">Frau</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <label>Titel</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.title"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Vorname</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.firstName"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Nachname</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.lastName"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <label>Funktion</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.role"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4">
                      <label>Telefon</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.phone"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>E-Mail</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.email"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Website</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.website"
                      />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-5">
                      <label>Strasse</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.street"
                      />
                    </div>
                    <div class="col-md-3">
                      <label>PLZ</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.zipCode"
                      />
                    </div>
                    <div class="col-md-4">
                      <label>Ort</label>
                      <input
                        readonly
                        type="text"
                        class="form-control"
                        v-model="contact.city"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <div class="project-component-form-row">
        <div class="project-component-form-section">
          <template v-if="project.id">
            <p>
              <strong>Erstellt:</strong> {{ formatDate(project.createdAt) }}
              <template v-if="project.updatedAt">
                <br />
                <strong>Aktualisiert:</strong>
                {{ formatDate(project.updatedAt) }}
              </template>
            </p>
          </template>
        </div>
        <div class="project-component-form-section"></div>
      </div>
    </div>

    <div
      class="project-component-overlay"
      v-if="showPreview"
      @click="showPreview = false"
    >
      <EmbedProjectsView
        @click.stop
        @clickClose="showPreview = false"
        :project="project"
        :locale="locale"
      >
      </EmbedProjectsView>
    </div>

    <transition name="fade">
      <Modal v-if="modal" :config="modal"></Modal>
    </transition>
  </div>
</template>

<script>
import { mapState, mapGetters } from "vuex";
import TagSelector from "./TagSelector";
import ImageSelector from "./ImageSelector";
import FileSelector from "./FileSelector";
import moment from "moment";
import { DatePicker } from "v-calendar";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import Modal from "./Modal";
import EmbedProjectsView from "./EmbedProjectsView";
import ProjectHeader from "../components/project/ProjectHeader.vue";
import FieldWrapper from "../components/project/FieldWrapper.vue";
import { tooltips } from "../utils/project-tooltips";
import TagSearchSelect from "./TagSearchSelect.vue";
import localWorkgroups from "../api/modules/local-workgroups";
import tags from "../api/modules/tags";

export default {
  components: {
    TagSelector,
    ImageSelector,
    FileSelector,
    DatePicker,
    EmbedProjectsView,
    Modal,
    ProjectHeader,
    FieldWrapper,
    TagSearchSelect,
  },
  computed: {
    ...mapState({
      selectedInboxItem: (state) => state.inbox.item,
      selectedProject: (state) => state.projects.project,
      programs: (state) => state.programs.all,
      instruments: (state) => state.instruments.all,
      topics: (state) => state.topics.all,
      geographicRegions: (state) => state.geographicRegions.all,
      businessSectors: (state) => state.businessSectors.all,
      tags: (state) => state.tags.all,
      countries: (state) => state.countries.all,
      states: (state) => state.states.all,
      inbox: (state) => state.inbox.all,
      localWorkgroups: (state) => state.localWorkgroups.all,
    }),
    ...mapGetters({
      isLoading: "loaders/isLoading",
      getTopicById: "topics/getById",
      getProgramById: "programs/getById",
      getInstrumentById: "instruments/getById",
      getCountryById: "countries/getById",
      getStateById: "states/getById",
      getGeographicRegionById: "geographicRegions/getById",
      getBusinessSectorById: "businessSectors/getById",
      localWorkgroupById: "localWorkgroups/getById",
      getTagById: "tags/getById",
    }),
  },
  data() {
    return {
      project: {
        title: "",
        projectCode: "",
        keywords: "",
        description: "",
        startDate: "",
        endDate: "",
        projectCosts: "",
        programs: [],
        attachments: [],
        financing: [],
        topics: [],
        tags: [],
        geographicRegions: [],
        businessSectors: [],
        instruments: [],
        countries: [],
        states: [],
        dates: [],
        videos: [],
        links: [],
        files: [],
        images: [],
        contacts: [],
        lat: null,
        lng: null,
        translations: {},
        isPublic: false,
        localWorkgroup: [],
        localWorkgroups: [],
        cooperationProjectAt: false,
        cooperationProjectEu: false,
        caseStudy: false,
        lePeriod: null,
        leFundingCategory: null,
        leFundingArticle: null,
        leFundingMethod: null,
        synergyFundTags: [],
        synergyGoalTags: [],
      },
      diff: null,
      locale: "de",
      showPreview: false,
      editor: ClassicEditor,
      editorConfig: {
        basicEntities: false,
        toolbar: {
          items: [
            "heading",
            "|",
            "bold",
            "italic",
            "link",
            "|",
            "numberedList",
            "bulletedList",
            "insertTable",
            "|",
            "undo",
            "redo",
          ],
        },
      },
      modal: null,
      tooltips: tooltips,
    };
  },
  created() {
    this.reload();
  },
  mounted() {
    document.querySelector(".backend-component-content").scrollTop = 0;
  },
  methods: {
    loadInboxItem(id) {
      return this.$store.dispatch("inbox/load", id);
    },
    loadProject(id) {
      return this.$store.dispatch("projects/load", id);
    },
    reload() {
      if (this.$route.name === "inbox_project") {
        this.$store.commit("inbox/set", {});
        this.$store.commit("projects/set", {});
        this.loadInboxItem(this.$route.params.id).then(() => {
          this.diff = this.selectedInboxItem.normalizedData;
          if (this.selectedInboxItem.internalId) {
            this.loadProject(this.selectedInboxItem.internalId).then(() => {
              this.project = { ...this.project, ...this.selectedProject };
              if (this.selectedInboxItem.status === "deleted") {
                this.diff = false;
              }
              // else if (this.selectedInboxItem.source === 'regiosuisse') {
              //     ['de', 'fr', 'it'].forEach((locale) => {
              //         this.mergeAll(locale);
              //     });
              // }
            });
          }
          // else if (this.selectedInboxItem.source === 'regiosuisse') {
          //     ['de', 'fr', 'it'].forEach((locale) => {
          //         this.project.isPublic = !!this.diff?.isPublic;
          //         this.mergeAll(locale);
          //     });
          // }
        });
      } else if (this.$route.params.id) {
        this.$store.commit("inbox/set", {});
        this.$store.commit("projects/set", {});
        this.loadProject(this.$route.params.id).then(() => {
          this.project = { ...this.project, ...this.selectedProject };
          // set local workgroup to the id, otherwhise the selected option is not shown
          if (this.project.localWorkgroup) {
            this.project.localWorkgroup = this.project.localWorkgroup.id;
          }
          if (!this.inbox.length) {
            this.$store.dispatch("inbox/loadAll").then(() => {
              this.warnIfInboxItemExists();
            });
          } else {
            this.warnIfInboxItemExists();
          }
        });
      } else {
        this.$store.commit("inbox/set", {});
        this.$store.commit("projects/set", {});
      }
    },
    warnIfInboxItemExists() {
      if (!this.project?.id === null) {
        return;
      }
      let inboxItem = this.inbox.find(
        (inboxItem) =>
          inboxItem.type === "project" &&
          inboxItem.internalId &&
          parseInt(inboxItem.internalId) === this.project?.id
      );

      if (!inboxItem) {
        return;
      }

      this.modal = {
        title: "Veraltete Daten",
        description:
          "Achtung: Sie betrachten ein Projekt für welches bereits Änderungen im Posteingang vorliegen. Möchten Sie stattdessen zum Eintrag im Posteingang wechseln?",
        actions: [
          {
            label: "Zum Eintrag im Posteingang",
            class: "success",
            onClick: () => {
              this.$router.push("/inbox/projects/" + inboxItem.id);
              this.modal = null;
              //this.reload();
            },
          },
          {
            label: "Trotzdem fortfahren",
            class: "warning",
            onClick: () => {
              this.modal = null;
            },
          },
        ],
      };
    },
    clickSaveProject() {
      if (
        this.project.startDate &&
        this.project.endDate &&
        moment(this.project.startDate).toISOString() >
          moment(this.project.endDate).toISOString()
      ) {
        this.modal = {
          title: "Ein Fehler ist aufgetreten",
          description: "Das End-Datum darf sich nicht vor dem Start-Datum befinden.",
          actions: [
            {
              label: "Verstanden",
              class: "error",
              onClick: () => {
                this.modal = null;
              },
            },
          ],
        };
        return;
      }

      if (!this.project.startDate) {
        this.project.startDate = null;
      }

      if (!this.project.endDate) {
        this.project.endDate = null;
      }

      let url = "projects/create";

      if (this.project.id) {
        url = "projects/update";
      }

      this.modal = {
        title: "Bearbeitung abschliessen",
        description:
          "Sie können die Änderungen freigeben und in ein Projekt übertragen, oder den Eintrag im Posteingang zwischenspeichern, um ihn später freizugeben.",
        actions: [
          {
            label: "Freigeben",
            class: "primary",
            onClick: () => {
              this.modal = null;
              return this.$store
                .dispatch(url, {
                  ...this.project,
                  inboxId: this.selectedInboxItem.id ? this.selectedInboxItem.id : null,
                  merge: true,
                  addToInbox: false,
                })
                .then(() => {
                  if (this.selectedInboxItem.id) {
                    return this.$router.push("/inbox");
                  }
                  this.$router.push("/projects");
                  this.$store.dispatch("tags/loadAll");
                });
            },
          },
          {
            label: "Zwischenspeichern",
            class: "success",
            onClick: () => {
              this.modal = null;
              return this.$store
                .dispatch(url, {
                  ...this.project,
                  inboxId: this.selectedInboxItem.id ? this.selectedInboxItem.id : null,
                  merge: false,
                  addToInbox: true,
                })
                .then(() => {
                  if (this.selectedInboxItem.id) {
                    return this.$router.push("/inbox");
                  }
                  this.$router.push("/projects");
                });
            },
          },
          {
            label: "Abbrechen",
            class: "warning",
            onClick: () => {
              this.modal = null;
            },
          },
        ],
      };

      if (!this.selectedInboxItem.id) {
        this.modal.description =
          "Möchten Sie den Eintrag direkt freigeben oder zur weiteren Überprüfung zum Posteingang hinzufügen?";
      }
    },
    compareTags(a, b) {
      if (!a || a.length !== (b || []).length) {
        return false;
      }
      for (let aEntry of a) {
        let match = (b || []).find((bEntry) => bEntry.id === aEntry.id);
        if (!match) {
          return false;
        }
      }

      return true;
    },
    compareOptions(a, b) {
      if (!a || a.length !== (b || []).length) {
        return false;
      }
      for (let aEntry of a) {
        let match = (b || []).find(
          (bEntry) => bEntry.id === aEntry.id && bEntry.value == aEntry.value
        );
        if (!match) {
          return false;
        }
      }

      return true;
    },
    compareObjects(a, b) {
      return JSON.stringify(a) === JSON.stringify(b);
    },
    compareContacts(a, b) {
      return (
        JSON.stringify(
          a
            ?.map((a) => Object.values(a))
            .flat(2)
            .filter((a) => a)
        ) ===
        JSON.stringify(
          b
            ?.map((b) => Object.values(b))
            .flat(2)
            .filter((b) => b)
        )
      );
    },
    compareDates(a, b) {
      return moment(a).isSame(moment(b));
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
    mergeAll(locale) {
      this.mergeFields("title", locale !== "de" ? locale : null);
      this.mergeFields("projectCode");
      this.mergeFields("keywords");
      this.mergeFields("description", locale !== "de" ? locale : null);
      this.mergeFields("startDate");
      this.mergeFields("endDate");
      this.mergeFields("projectCosts");
      this.mergeFields("lat");
      this.mergeFields("lng");
      this.mergeOptions("programs");
      this.mergeOptions("attachments");
      this.mergeOptions("financing");
      this.mergeOptions("topics");
      this.mergeOptions("tags");
      this.mergeOptions("geographicRegions");
      this.mergeOptions("instruments");
      this.mergeOptions("countries");
      this.mergeOptions("states");
      this.mergeOptions("businessSectors");
      this.mergeOptions("dates");
      this.mergeOptions("images");
      this.mergeOptions("videos", locale !== "de" ? locale : null);
      this.mergeOptions("links", locale !== "de" ? locale : null);
      this.mergeOptions("files", locale !== "de" ? locale : null);
      this.mergeOptions("contacts", locale !== "de" ? locale : null);
      this.mergeOption("localWorkgroup");
      this.mergeOptions("localWorkgroups");
      this.mergeFields("caseStudy");
      this.mergeFields("cooperationProjectAt");
      this.mergeFields("cooperationProjectEu");
      this.mergeFields("exemplary");
      this.mergeFields("initialContext");
      this.mergeFields("initialContextGoals");
      this.mergeFields("additionalValue");
      this.mergeFields("additionalValueResult");
      this.mergeFields("innovations");
      this.mergeFields("integrationYoungCitizen");
      this.mergeFields("integrationFemaleCitizen");
      this.mergeFields("integrationMinorities");
      this.mergeFields("learningExperience");
      this.mergeFields("transferable");
      this.mergeFields("transferableDetails");
      this.mergeFields("fundingMethod");
      this.mergeFields("fundingMethodStakeholders");
      this.mergeFields("resultsQuantity");
      this.mergeFields("resultsQuality");
      this.mergeOptions("synergyFundTags");
      this.mergeOptions("synergyGoalTags");
      this.mergeFundingStructureFields();
    },
    mergeFundingStructureFields() {
      if (this.diff.lePeriod !== this.project.lePeriod) {
        this.project.lePeriod = this.diff.lePeriod;
      }
      if (this.diff.leFundingCategory !== this.project.leFundingCategory) {
        this.project.leFundingCategory = this.diff.leFundingCategory;
      }
      if (this.diff.leFundingArticle !== this.project.leFundingArticle) {
        this.project.leFundingArticle = this.diff.leFundingArticle;
      }
      if (this.diff.leFundingMethod !== this.project.leFundingMethod) {
        this.project.leFundingMethod = this.diff.leFundingMethod;
      }
    },
    mergeFields(field, locale = null) {
      if (locale) {
        if (!this.project.translations[locale]) {
          this.project.translations[locale] = {};
        }
        this.project.translations[locale][field] = this.diff[field];
        if (this.diff.translations[locale] && this.diff.translations[locale][field]) {
          this.project.translations[locale][field] = this.diff.translations[locale][
            field
          ];
        }
        return;
      }
      if (this.diff[field] && this.project[field] !== this.diff[field]) {
        this.project[field] = this.diff[field];
      }
    },
    mergeOption(field, locale = null) {
      if (locale) {
        if (!this.project.translations[locale]) {
          this.project.translations[locale] = {};
        }
        this.project.translations[locale][field] = this.diff[field];
        if (this.diff.translations[locale] && this.diff.translations[locale][field]) {
          this.project.translations[locale][field] = this.diff.translations[locale][
            field
          ];
        }
        return;
      }
      if (this.diff[field] && this.project[field] !== this.diff[field]) {
        this.project[field] = this.diff[field];
      }
    },
    mergeOptions(field, locale = null) {
      if (locale) {
        if (!this.project.translations[locale]) {
          this.project.translations[locale] = {};
        }
        this.project.translations[locale][field] = [];
        for (let option of this.diff[field] || []) {
          this.project.translations[locale][field].push({ ...option });
        }
        if (this.diff.translations[locale] && this.diff.translations[locale][field]) {
          this.project.translations[locale][field] = [];
          for (let option of this.diff.translations[locale][field] || []) {
            this.project.translations[locale][field].push({ ...option });
          }
        }
        return;
      }
      this.project[field] = [];
      for (let option of this.diff[field] || []) {
        this.project[field].push({ ...option });
      }
    },
    clickDeleteProject() {
      this.modal = {
        title: "Löschen bestätigen",
        description:
          "Sind Sie sicher dass Sie dieses Projekt unwiderruflich löschen möchten?",
        actions: [
          {
            label: "Endgültig löschen",
            class: "error",
            onClick: () => {
              this.modal = null;
              this.$store.dispatch("projects/delete", this.project.id).then(() => {
                this.$router.back();
              });
            },
          },
          {
            label: "Abbrechen",
            class: "warning",
            onClick: () => {
              this.modal = null;
            },
          },
        ],
      };

      if (this.selectedInboxItem.id) {
        this.modal.description +=
          " " +
          'Wenn Sie nur den Eintrag aus dem Posteingang entfernen möchten, wählen Sie stattdessen "Update verwerfen".';
      }
    },
    clickDismissDiff() {
      this.modal = {
        title: "Verwerfen bestätigen",
        description: "Sind Sie sicher dass Sie dieses Update verwerfen möchten?",
        actions: [
          {
            label: "Update verwerfen",
            class: "error",
            onClick: () => {
              this.modal = null;
              this.$store.dispatch("inbox/delete", this.selectedInboxItem.id).then(() => {
                this.$router.back();
              });
            },
          },
          {
            label: "Abbrechen",
            class: "warning",
            onClick: () => {
              this.modal = null;
            },
          },
        ],
      };
    },
    showPreviewModal() {
      this.showPreview = true;
    },
    updateImages(images) {
      this.project.images = images;
    },
    updateFiles(files) {
      this.project.files = files;
    },
    updateTranslatedFiles(files) {
      this.project.translations[this.locale].files = files;
    },
    parseYoutubeId(url) {
      const result = (url || "").split(/(vi\/|v=|\/v\/|youtu\.be\/|\/embed\/)/);
      return result[2] !== undefined ? result[2].split(/[^0-9a-z_\-]/i)[0] : false;
    },
    clickLocale(locale) {
      if (this.locale !== "de" && locale === "de") {
        if (!this.project.title) {
          this.project.title = "";
        }
      }

      this.locale = locale;

      if (!this.isTranslationModeEnabled()) {
        return;
      }

      if (!this.project.translations[locale]) {
        this.project.translations[locale] = {};
      }
      if (!this.project.translations[locale].title) {
        this.project.translations[locale].title = "";
      }
      if (!this.project.translations[locale].description) {
        this.project.translations[locale].description = "";
      }
      if (!this.project.translations[locale].links) {
        this.project.translations[locale].links = [];
      }
      if (!this.project.translations[locale].files) {
        this.project.translations[locale].files = [];
      }
      if (!this.project.translations[locale].videos) {
        this.project.translations[locale].videos = [];
      }
      if (!this.project.translations[locale].contacts) {
        this.project.translations[locale].contacts = [];
      }
    },
    isTranslationModeEnabled() {
      return this.locale !== "de";
    },
    formatDate(date) {
      if (date && moment(date)) {
        return moment(date).format("DD.MM.YYYY");
      }
    },
    filterNumber(input) {
      return parseFloat(input.toString().replaceAll(",", ".")) || 0.0;
    },
    setCooperationProjectAt(value) {
      this.project.cooperationProjectAt = value;
    },
  },
};
</script>
