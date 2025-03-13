<template>
  <div class="project-import-preview-component">
    <div class="project-import-preview-component-title">
      <h2>Import Vorschau</h2>

      <transition name="fade" mode="out-in">
        <div class="loading-indicator" v-if="isLoading('projectImport')"></div>
      </transition>

      <div class="project-import-preview-component-title-actions">
        <router-link :to="'/projects/import'" class="button">
          <i class="material-icons">arrow_back</i>
          Zurück zur Importliste
        </router-link>
      </div>
    </div>

    <div class="project-import-preview-component-content">
      <!-- Import Details Section -->
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3>
                <i class="material-icons">info</i>
                Import Details
              </h3>
            </div>
            <div class="card-body">
              <!-- Data Loading State -->
              <div v-if="isDataLoading || isProcessing" class="processing-status">
                <div class="processing-status-header">
                  <i class="material-icons processing-icon">sync</i>
                  <h4>{{ isProcessing ? 'Daten werden importiert' : 'Daten werden geladen' }}</h4>
                </div>
                <div class="progress">
                  <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" 
                    style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                    Bitte warten...
                  </div>
                </div>
              </div>
              
              <!-- Import Details Content -->
              <div v-else-if="importData" class="import-details-summary">
                <div class="import-details-card">
                  <div class="import-details-card-header">
                    <i class="material-icons">description</i>
                    <h4>Allgemeine Informationen</h4>
                  </div>
                  <div class="import-details-card-body">
                    <div class="detail-item">
                      <span class="detail-label">ID:</span>
                      <span class="detail-value">{{ importData.id }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Dateiname:</span>
                      <span class="detail-value">{{ importData.originalFilename }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Status:</span>
                      <span class="detail-value">
                        <span :class="{
                          'badge-success': importData.status === 'completed',
                          'badge-warning': importData.status === 'pending' || importData.status === 'processing',
                          'badge-danger': importData.status === 'failed'
                        }" class="badge">
                          {{ getStatusLabel(importData.status) }}
                        </span>
                      </span>
                    </div>
                  </div>
                </div>

                <div class="import-details-card">
                  <div class="import-details-card-header">
                    <i class="material-icons">schedule</i>
                    <h4>Zeitinformationen</h4>
                  </div>
                  <div class="import-details-card-body">
                    <div class="detail-item">
                      <span class="detail-label">Erstellt am:</span>
                      <span class="detail-value">{{ formatDate(importData.createdAt) }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Aktualisiert am:</span>
                      <span class="detail-value">{{ formatDate(importData.updatedAt) }}</span>
                    </div>
                  </div>
                </div>
                
                <div class="import-details-card">
                  <div class="import-details-card-header">
                    <i class="material-icons">analytics</i>
                    <h4>Statistik</h4>
                  </div>
                  <div class="import-details-card-body">
                    <div class="detail-item">
                      <span class="detail-label">Zeilen gesamt:</span>
                      <span class="detail-value">{{ importData.totalRows }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Zeilen verarbeitet:</span>
                      <span class="detail-value">{{ importData.processedRows }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Zeilen erfolgreich:</span>
                      <span class="detail-value">{{ importData.successfulRows }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Zeilen mit Fehlern:</span>
                      <span class="detail-value">{{ importData.errorRows }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- LE Period Selection Section -->
      <!-- Commented out as we now automatically select LE Period based on importerType 
      <div class="row mt-4" v-if="!isDataLoading && !isProcessing && importData && importData.status === 'pending'">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3>
                <i class="material-icons">event</i>
                LE Periode
              </h3>
            </div>
            <div class="card-body">
              <div class="le-period-info">
                <i class="material-icons">info</i>
                <p>
                  Bitte wählen Sie eine LE Period aus, die allen importierten Projekten zugewiesen werden soll.
                  Die LE Period ist erforderlich, die Fördermethoden werden automatisch anhand der LE Period zugewiesen.
                </p>
              </div>

              <div class="le-period-selection">
                <div class="form-group">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="le-period-select">
                          <i class="material-icons">event</i>
                          LE Period auswählen:
                        </label>
                        <div class="select-wrapper">
                          <select id="le-period-select" class="form-control" v-model="selectedLePeriodId"
                            :disabled="isProcessing" required>
                            <option value="">Keine LE Period auswählen</option>
                            <option v-for="period in lePeriods" :key="period.id" :value="period.id">
                              {{ period.name }}
                            </option>
                          </select>
                        </div>
                        <div class="selected-period-info" v-if="selectedLePeriodId">
                          <i class="material-icons">check_circle</i>
                          <span>LE Period ausgewählt: {{ getSelectedPeriodName() }}</span>
                        </div>
                        <div class="validation-error" v-else>
                          <i class="material-icons">warning</i>
                          <span>Eine LE Period muss ausgewählt werden, bevor der Import gestartet werden kann.</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      -->

      <!-- New info banner about automatic LE Period selection -->
      <div class="row mt-4" v-if="!isDataLoading && !isProcessing && importData && importData.status === 'pending'">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3>
                <i class="material-icons">event</i>
                LE Periode
              </h3>
            </div>
            <div class="card-body">
              <div class="le-period-info">
                <i class="material-icons">info</i>
                <p>
                  Basierend auf dem ausgewählten Import-Typ wird die passende LE Period automatisch zugewiesen: 
                  <strong>{{ getSelectedPeriodName() }}</strong>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Preview Data Section -->
      <div class="row mt-4" v-if="previewData && previewData.length > 0 && !isProcessing">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3>
                <i class="material-icons">preview</i>
                Vorschau der zu importierenden Projekte
              </h3>
              <div class="card-header-actions">
                <button class="button primary" @click="startImport"
                  :disabled="isProcessing || !importData || importData.status !== 'pending' || !selectedLePeriodId">
                  <i class="material-icons">play_arrow</i>
                  {{ isProcessing ? 'Wird importiert...' : 'Import starten' }}
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="preview-filters" v-if="!isProcessing && previewData.length > 0">
                <div class="form-group">
                  <label for="filter-status">
                    <i class="material-icons">filter_list</i>
                    Nach Status filtern:
                  </label>
                  <div class="select-wrapper">
                    <select id="filter-status" class="form-control" v-model="statusFilter">
                      <option value="all">Alle anzeigen</option>
                      <option value="valid">Nur gültige</option>
                      <option value="warning">Nur mit Warnungen</option>
                      <option value="error">Nur mit Fehlern</option>
                    </select>
                  </div>
                </div>

                <div class="preview-stats">
                  <div class="preview-stat">
                    <span class="badge badge-success">{{ validCount }}</span>
                    <span>Gültig</span>
                  </div>
                  <div class="preview-stat">
                    <span class="badge badge-warning">{{ warningCount }}</span>
                    <span>Warnungen</span>
                  </div>
                  <div class="preview-stat">
                    <span class="badge badge-danger">{{ errorCount }}</span>
                    <span>Fehler</span>
                  </div>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Zeile</th>
                      <th>Titel</th>
                      <th>Beschreibung</th>
                      <th>Projektcode</th>
                      <th>Start Datum</th>
                      <th>End Datum</th>
                      <th>LE-Kategorie</th>
                      <th>Lokale Arbeitsgruppe</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in filteredPreviewData" :key="index" @click="showRowDetails(item)"
                      class="clickable-row" :class="{
                        'success': item.status === 'valid',
                        'warning': item.status === 'warning',
                        'error': item.status === 'error'
                      }">
                      <td>{{ item.rowNumber }}</td>
                      <td>
                        <div class="preview-title">
                          <i class="material-icons" v-if="item.status === 'valid'">check_circle</i>
                          <i class="material-icons" v-else-if="item.status === 'warning'">warning</i>
                          <i class="material-icons" v-else>error</i>
                          <span>{{ item.title || 'Kein Titel' }}</span>
                        </div>
                      </td>
                      <td>{{ truncateText(item.description || (item._rawData ? item._rawData.description : ''), 100) }}
                      </td>
                      <td>{{ item.projectCode }}</td>
                      <td>{{ formatDate(item.startDate) }}</td>
                      <td>{{ formatDate(item.endDate) }}</td>
                      <td>{{ item.leCategory }}</td>
                      <td>{{ item.localWorkgroup }}</td>
                      <td>
                        <span :class="{
                          'badge-success': item.status === 'valid',
                          'badge-warning': item.status === 'warning',
                          'badge-danger': item.status === 'error'
                        }" class="badge">
                          {{ getItemStatusLabel(item.status) }}
                        </span>
                        <div v-if="item.message" class="item-message">
                          {{ item.message }}
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-4" v-else-if="!isDataLoading && !isProcessing && importData">
        <div class="col-md-12">
          <div class="card">
            <div class="card-body">
              <p>Keine Vorschaudaten verfügbar. Bitte laden Sie die Vorschau neu.</p>
              <button class="button" @click="loadPreviewData">Vorschau laden</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Row Details Modal -->
      <div class="modal" v-if="showRowDetailsModal">
        <div class="modal-content large-modal">
          <div class="modal-header">
            <h3>
              <i class="material-icons">assignment</i>
              Projekt Details
            </h3>
            <button class="close" @click="showRowDetailsModal = false">
              <i class="material-icons">close</i>
            </button>
          </div>
          <div class="modal-body">
            <div v-if="selectedRow">
              <div class="row-details-status" :class="'status-' + selectedRow.status">
                <i class="material-icons" v-if="selectedRow.status === 'valid'">check_circle</i>
                <i class="material-icons" v-else-if="selectedRow.status === 'warning'">warning</i>
                <i class="material-icons" v-else>error</i>
                <span class="status-text">{{ getItemStatusLabel(selectedRow.status) }}</span>
                <span class="status-message" v-if="selectedRow.message">{{ selectedRow.message }}</span>
              </div>

              <div class="row-details-tabs">
                <div class="tab-buttons">
                  <button class="tab-button" :class="{ active: activeTab === 'general' }"
                    @click="activeTab = 'general'">
                    <i class="material-icons">info</i>
                    Allgemeine Informationen
                  </button>
                  <button class="tab-button" :class="{ active: activeTab === 'details' }"
                    @click="activeTab = 'details'">
                    <i class="material-icons">description</i>
                    Details
                  </button>
                  <button class="tab-button" :class="{ active: activeTab === 'contacts' }"
                    @click="activeTab = 'contacts'"
                    v-if="selectedRow._rawData && selectedRow._rawData.contacts && selectedRow._rawData.contacts.length">
                    <i class="material-icons">contacts</i>
                    Kontakte
                  </button>
                  <button class="tab-button" :class="{ active: activeTab === 'links' }" @click="activeTab = 'links'"
                    v-if="hasLinksOrVideos">
                    <i class="material-icons">link</i>
                    Links & Videos
                  </button>
                  <button class="tab-button" :class="{ active: activeTab === 'raw' }" @click="activeTab = 'raw'">
                    <i class="material-icons">code</i>
                    Rohdaten
                  </button>
                </div>

                <div class="tab-content">
                  <!-- General Tab -->
                  <div class="tab-pane" :class="{ active: activeTab === 'general' }">
                    <div class="row-details-grid">
                      <div class="row-details-card">
                        <div class="row-details-card-header">
                          <i class="material-icons">assignment</i>
                          <h4>Projektinformationen</h4>
                        </div>
                        <div class="row-details-card-body">
                          <div class="detail-item">
                            <span class="detail-label">Zeile:</span>
                            <span class="detail-value">{{ selectedRow.rowNumber }}</span>
                          </div>
                          <div class="detail-item">
                            <span class="detail-label">Titel:</span>
                            <span class="detail-value">{{ selectedRow.title }}</span>
                          </div>
                          <div class="detail-item">
                            <span class="detail-label">Projektcode:</span>
                            <span class="detail-value">{{ selectedRow.projectCode }}</span>
                          </div>
                          <div class="detail-item">
                            <span class="detail-label">LE-Kategorie:</span>
                            <span class="detail-value">{{ selectedRow.leCategory }}</span>
                          </div>
                          <div class="detail-item">
                            <span class="detail-label">Lokale Arbeitsgruppe:</span>
                            <span class="detail-value">{{ selectedRow.localWorkgroup }}</span>
                          </div>
                          <div class="detail-item">
                            <span class="detail-label">Start Datum:</span>
                            <span class="detail-value">{{ formatDate(selectedRow.startDate) }}</span>
                          </div>
                          <div class="detail-item">
                            <span class="detail-label">End Datum:</span>
                            <span class="detail-value">{{ formatDate(selectedRow.endDate) }}</span>
                          </div>
                        </div>
                      </div>

                      <div class="row-details-card"
                        v-if="selectedRow._rawData && selectedRow._rawData.topicNames && selectedRow._rawData.topicNames.length">
                        <div class="row-details-card-header">
                          <i class="material-icons">label</i>
                          <h4>Themen</h4>
                        </div>
                        <div class="row-details-card-body">
                          <ul class="tags-list">
                            <li v-for="(topic, i) in selectedRow._rawData.topicNames" :key="'topic-' + i">
                              {{ topic }}
                            </li>
                          </ul>
                        </div>
                      </div>

                      <div class="row-details-card"
                        v-if="selectedRow._rawData && selectedRow._rawData.tagNames && selectedRow._rawData.tagNames.length">
                        <div class="row-details-card-header">
                          <i class="material-icons">local_offer</i>
                          <h4>Schlagworte</h4>
                        </div>
                        <div class="row-details-card-body">
                          <ul class="tags-list">
                            <li v-for="(tag, i) in selectedRow._rawData.tagNames" :key="'tag-' + i">
                              {{ tag }}
                            </li>
                          </ul>
                        </div>
                      </div>

                      <div class="row-details-card"
                        v-if="selectedRow._rawData && selectedRow._rawData.states && selectedRow._rawData.states.length">
                        <div class="row-details-card-header">
                          <i class="material-icons">place</i>
                          <h4>Bundesländer</h4>
                        </div>
                        <div class="row-details-card-body">
                          <ul class="tags-list">
                            <li v-for="(state, i) in selectedRow._rawData.states" :key="'state-' + i">
                              {{ state.name }}
                            </li>
                          </ul>
                        </div>
                      </div>

                      <div class="row-details-card"
                        v-if="selectedRow._rawData && selectedRow._rawData.financing && selectedRow._rawData.financing.length">
                        <div class="row-details-card-header">
                          <i class="material-icons">euro_symbol</i>
                          <h4>Finanzierung</h4>
                        </div>
                        <div class="row-details-card-body">
                          <div class="detail-item" v-if="selectedRow._rawData.projectCosts">
                            <span class="detail-label">Projektkosten:</span>
                            <span class="detail-value">{{ selectedRow._rawData.projectCosts }} €</span>
                          </div>
                          <div class="detail-item" v-for="(finance, i) in selectedRow._rawData.financing"
                            :key="'finance-' + i">
                            <span class="detail-label">{{ getFinancingLabel(finance.id) }}:</span>
                            <span class="detail-value">{{ finance.value }}%</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="row-details-description">
                      <h4>
                        <i class="material-icons">subject</i>
                        Beschreibung
                      </h4>
                      <div class="description-box">
                        {{ selectedRow.description || selectedRow._rawData.description || 'Keine Beschreibung vorhanden'
                        }}
                      </div>
                    </div>
                  </div>

                  <!-- Details Tab -->
                  <div class="tab-pane" :class="{ active: activeTab === 'details' }">
                    <div class="row-details-grid">
                      <div class="row-details-card" v-for="(value, key) in filteredDetails" :key="key">
                        <div class="row-details-card-header">
                          <i class="material-icons">assignment</i>
                          <h4>{{ formatDetailKey(key) }}</h4>
                        </div>
                        <div class="row-details-card-body">
                          <div class="detail-value">{{ formatDetailValue(value) }}</div>
                        </div>
                      </div>
                    </div>

                    <div v-if="Object.keys(filteredDetails).length === 0" class="no-data">
                      <i class="material-icons">info</i>
                      <p>Keine weiteren Details vorhanden</p>
                    </div>
                  </div>

                  <!-- Contacts Tab -->
                  <div class="tab-pane" :class="{ active: activeTab === 'contacts' }">
                    <div class="contacts-grid"
                      v-if="selectedRow._rawData && selectedRow._rawData.contacts && selectedRow._rawData.contacts.length">
                      <div class="contact-card" v-for="(contact, i) in selectedRow._rawData.contacts"
                        :key="'contact-' + i">
                        <div class="contact-card-header">
                          <i class="material-icons">person</i>
                          <h4>{{ (contact.firstName || '') + ' ' + (contact.lastName || '') || 'Unbenannter Kontakt' }}
                          </h4>
                        </div>
                        <div class="contact-card-body">
                          <div class="detail-item" v-if="contact.name">
                            <span class="detail-label">Organisation:</span>
                            <span class="detail-value">{{ contact.name }}</span>
                          </div>
                          <div class="detail-item" v-if="contact.email">
                            <span class="detail-label">E-Mail:</span>
                            <span class="detail-value">
                              <a :href="'mailto:' + contact.email">{{ contact.email }}</a>
                            </span>
                          </div>
                          <div class="detail-item" v-if="contact.phone">
                            <span class="detail-label">Telefon:</span>
                            <span class="detail-value">{{ contact.phone }}</span>
                          </div>
                          <div class="detail-item" v-if="contact.street || contact.zipCode || contact.city">
                            <span class="detail-label">Adresse:</span>
                            <span class="detail-value">
                              {{ [contact.street, contact.zipCode, contact.city].filter(Boolean).join(', ') }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="no-data" v-else>
                      <i class="material-icons">info</i>
                      <p>Keine Kontaktdaten vorhanden</p>
                    </div>
                  </div>

                  <!-- Links Tab -->
                  <div class="tab-pane" :class="{ active: activeTab === 'links' }">
                    <div class="row-details-grid">
                      <div class="row-details-card"
                        v-if="selectedRow._rawData && selectedRow._rawData.links && selectedRow._rawData.links.length">
                        <div class="row-details-card-header">
                          <i class="material-icons">link</i>
                          <h4>Links</h4>
                        </div>
                        <div class="row-details-card-body">
                          <ul class="links-list">
                            <li v-for="(link, i) in selectedRow._rawData.links" :key="'link-' + i" class="link-item">
                              <div class="link-content">
                                <div v-if="link.label" class="link-label">{{ link.label }}</div>
                                <a :href="link.url" target="_blank" class="link-url">
                                  {{ link.url }}
                                  <i class="material-icons">open_in_new</i>
                                </a>
                              </div>
                            </li>
                          </ul>
                        </div>
                      </div>

                      <div class="row-details-card"
                        v-if="selectedRow._rawData && selectedRow._rawData.videos && selectedRow._rawData.videos.length">
                        <div class="row-details-card-header">
                          <i class="material-icons">videocam</i>
                          <h4>Videos</h4>
                        </div>
                        <div class="row-details-card-body">
                          <ul class="links-list">
                            <li v-for="(video, i) in selectedRow._rawData.videos" :key="'video-' + i" class="link-item">
                              <div class="link-content">
                                <div v-if="video.label" class="link-label">{{ video.label }}</div>
                                <a :href="video.url" target="_blank" class="link-url">
                                  {{ video.url }}
                                  <i class="material-icons">videocam</i>
                                </a>
                              </div>
                            </li>
                          </ul>
                        </div>
                      </div>
                    </div>

                    <div class="no-data" v-if="!hasLinksOrVideos">
                      <i class="material-icons">info</i>
                      <p>Keine Links oder Videos vorhanden</p>
                    </div>
                  </div>

                  <!-- Raw Data Tab -->
                  <div class="tab-pane" :class="{ active: activeTab === 'raw' }">
                    <div class="raw-data-controls">
                      <button class="button small" @click="toggleRawData">
                        {{ showRawData ? 'Rohdaten ausblenden' : 'Rohdaten anzeigen' }}
                      </button>
                      <div class="raw-data-info">
                        <i class="material-icons">info</i>
                        <span>Zeigt alle unverarbeiteten Daten aus der Excel-Datei</span>
                      </div>
                    </div>
                    <pre v-if="showRawData" class="raw-data">{{ JSON.stringify(selectedRow._rawData, null, 2) }}</pre>
                    <div v-else class="no-data">
                      <p>Klicken Sie auf "Rohdaten anzeigen", um die vollständigen Daten zu sehen.</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="modal-actions">
                <button class="button primary" @click="showRowDetailsModal = false">Schließen</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      importId: null,
      importData: null,
      previewData: [],
      isProcessing: false,
      processedRows: 0,
      totalRows: 0,
      pollingInterval: null,
      showRowDetailsModal: false,
      selectedRow: null,
      showRawData: false,
      isDataLoading: false,
      lePeriods: [],
      selectedLePeriodId: '',
      statusFilter: 'all',
      validCount: 0,
      warningCount: 0,
      errorCount: 0,
      activeTab: 'general',
      filteredDetails: {},
      importStartTime: null,
      importStartedNotificationShown: false
    };
  },
  created() {
    // Get the import ID from the route params
    this.importId = this.$route.params.id;
    
    // Start loading indicators
    this.$store.commit('loaders/showLoader', 'projectImport');
    this.isDataLoading = true;
    
    // Load data
    Promise.all([
      this.fetchImportData(),
      this.loadPreviewData(),
      this.fetchLePeriods()
    ]).then(() => {
      // Set the LE Period automatically based on importerType
      this.setLePeriodBasedOnImporterType();
      
      // Check if import is already processing
      if (this.importData && this.importData.status === 'processing') {
        this.isProcessing = true;
        this.processedRows = this.importData.processedRows || 0;
        this.totalRows = this.importData.totalRows || 0;
        
        // Start polling for updates
        this.startPolling();
        
        // Show a notification that the import is in progress
        this.$store.dispatch('notifications/add', {
          type: 'info',
          message: 'Ein Import ist bereits in Bearbeitung. Der Fortschritt wird automatisch aktualisiert.'
        });
      } else if (this.importData && this.importData.status === 'completed') {
        // If already completed, just update the UI
        this.isProcessing = false;
      }
    }).finally(() => {
      this.isDataLoading = false;
      this.$store.commit('loaders/hideLoader', 'projectImport');
    });
  },
  beforeUnmount() {
    // Clear any polling intervals when component is destroyed
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval);
    }
  },
  computed: {
    filteredPreviewData() {
      let data = [];

      if (this.statusFilter === 'all') {
        data = this.previewData;
      } else if (this.statusFilter === 'valid') {
        data = this.previewData.filter(item => item.status === 'valid');
      } else if (this.statusFilter === 'warning') {
        data = this.previewData.filter(item => item.status === 'warning');
      } else if (this.statusFilter === 'error') {
        data = this.previewData.filter(item => item.status === 'error');
      }

      // Add LE-Category information to each item
      return data.map(item => {
        const result = { ...item };

        // Add LE-Category name if available
        if (item.payload && item.payload.leFundingCategoryName) {
          result.leCategory = item.payload.leFundingCategoryName;
        } else if (item.payload && item.payload.leFundingCategoryId) {
          result.leCategory = `Kategorie ID: ${item.payload.leFundingCategoryId}`;
        } else {
          result.leCategory = '-';
        }

        // Add LocalWorkgroup name if available
        if (item.payload && item.payload.localWorkgroupName) {
          result.localWorkgroup = item.payload.localWorkgroupName;
        } else if (item.payload && item.payload.localWorkgroupId) {
          result.localWorkgroup = `Arbeitsgruppe ID: ${item.payload.localWorkgroupId}`;
        } else {
          result.localWorkgroup = '-';
        }

        return result;
      });
    },
    hasLinksOrVideos() {
      if (this.selectedRow && this.selectedRow._rawData) {
        return (this.selectedRow._rawData.links && this.selectedRow._rawData.links.length > 0) ||
          (this.selectedRow._rawData.videos && this.selectedRow._rawData.videos.length > 0);
      }
      return false;
    }
  },
  watch: {
    previewData: {
      immediate: true,
      handler(newData) {
        if (newData && newData.length > 0) {
          this.validCount = newData.filter(item => item.status === 'valid').length;
          this.warningCount = newData.filter(item => item.status === 'warning').length;
          this.errorCount = newData.filter(item => item.status === 'error').length;
        } else {
          this.validCount = 0;
          this.warningCount = 0;
          this.errorCount = 0;
        }
      }
    },
    selectedRow: {
      handler(newRow) {
        if (newRow && newRow._rawData) {
          // Make sure description is available
          if (!newRow.description && newRow._rawData.description) {
            newRow.description = newRow._rawData.description;
          } else if (newRow.description && !newRow._rawData.description) {
            newRow._rawData.description = newRow.description;
          }

          // Filter out specific keys that are displayed in other sections
          const excludedKeys = ['contacts', 'links', 'videos', 'states', 'topicNames', 'tagNames', 'financing',
            'title', 'projectCode', 'startDate', 'endDate'];

          this.filteredDetails = Object.entries(newRow._rawData)
            .filter(([key]) => !excludedKeys.includes(key))
            .reduce((obj, [key, value]) => {
              obj[key] = value;
              return obj;
            }, {});

        } else {
          this.filteredDetails = {};
        }
      },
      immediate: true
    }
  },
  methods: {
    isLoading(key) {
      return this.$store.getters['loaders/isLoading'](key);
    },
    async fetchImportData() {
      this.isDataLoading = true;
      
      try {
        const response = await fetch(`/api/v1/project-imports/${this.importId}`, {
          method: 'GET',
          credentials: 'include'
        });

        const data = await response.json();

        if (response.ok) {
          this.importData = data;
          this.totalRows = data.totalRows || 0;
          this.processedRows = data.processedRows || 0;

          // If the import is already processing, set the processing state
          if (data.status === 'processing') {
            this.isProcessing = true;
            this.processedRows = data.processedRows || 0;
            this.totalRows = data.totalRows || 0;
            
            // Start polling for updates
            this.startPolling();
            
            // Show a notification that the import is in progress
            this.$store.dispatch('notifications/add', {
              type: 'info',
              message: 'Ein Import ist bereits in Bearbeitung. Der Fortschritt wird automatisch aktualisiert.'
            });
          } else if (data.status === 'completed') {
            // If already completed, just update the UI
            this.isProcessing = false;
          }
          
          return data;
        } else {
          this.$store.dispatch('notifications/add', {
            type: 'error',
            message: data.error || 'Beim Laden der Import-Details ist ein Fehler aufgetreten.'
          });
          throw new Error(data.error || 'Beim Laden der Import-Details ist ein Fehler aufgetreten.');
        }
      } catch (error) {
        this.$store.dispatch('notifications/add', {
          type: 'error',
          message: 'Beim Laden der Import-Details ist ein Fehler aufgetreten.'
        });
        throw error;
      }
    },
    async loadPreviewData() {
      this.isDataLoading = true;
      
      try {
        // This would be a new API endpoint to get preview data
        const response = await fetch(`/api/v1/project-imports/${this.importId}/preview`, {
          method: 'GET',
          credentials: 'include'
        });

        const data = await response.json();

        if (response.ok) {
          // Store the raw data for each row
          this.previewData = data.map(item => {
            // Ensure description is available
            if (item.payload && item.payload.description && !item.description) {
              item.description = item.payload.description;
            }

            return {
              ...item,
              _rawData: item.payload || {},
              description: item.description || (item.payload ? item.payload.description : '') || ''
            };
          });

          return this.previewData;
        } else {
          this.$store.dispatch('notifications/add', {
            type: 'error',
            message: data.error || 'Beim Laden der Vorschaudaten ist ein Fehler aufgetreten.'
          });
          throw new Error(data.error || 'Beim Laden der Vorschaudaten ist ein Fehler aufgetreten.');
        }
      } catch (error) {
        this.$store.dispatch('notifications/add', {
          type: 'error',
          message: 'Beim Laden der Vorschaudaten ist ein Fehler aufgetreten.'
        });
        throw error;
      }
    },
    async startImport() {
      // We no longer need to check if a LE period is selected as it's done automatically
      
      if (!confirm('Sind Sie sicher, dass Sie den Import starten möchten?')) {
        return;
      }

      // Set processing state to show the import is running
      this.isProcessing = true;
      this.$store.commit('loaders/showLoader', 'projectImport');
      
      // Reset notification flags
      this.importStartTime = Date.now();
      this.importStartedNotificationShown = false;
      
      // Show a notification that the import is starting
      this.$store.dispatch('notifications/add', {
        type: 'info',
        message: 'Der Import wird gestartet. Bitte haben Sie etwas Geduld...'
      });

      try {
        const response = await fetch(`/api/v1/project-imports/${this.importId}/process`, {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            lePeriodId: this.selectedLePeriodId
          })
        });

        const data = await response.json();

        if (response.ok) {
          this.importData = data;

          // Start polling for updates
          this.startPolling();

          // Only show this notification if the import hasn't completed within 2 seconds
          setTimeout(() => {
            // If we're still processing after 2 seconds, show the "import started" notification
            if (this.isProcessing && !this.importStartedNotificationShown) {
              this.importStartedNotificationShown = true;
              this.$store.dispatch('notifications/add', {
                type: 'success',
                message: 'Der Import wurde gestartet und läuft im Hintergrund. Der Fortschritt wird automatisch aktualisiert.'
              });
            }
          }, 2000);
        } else {
          this.isProcessing = false;

          this.$store.dispatch('notifications/add', {
            type: 'error',
            message: data.error || 'Beim Starten des Imports ist ein Fehler aufgetreten.'
          });
        }
      } catch (error) {
        this.isProcessing = false;

        this.$store.dispatch('notifications/add', {
          type: 'error',
          message: 'Beim Starten des Imports ist ein Fehler aufgetreten.'
        });
      } finally {
        this.$store.commit('loaders/hideLoader', 'projectImport');
      }
    },
    startPolling() {
      // Clear any existing polling interval
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
      }
      
      
      // Poll for updates every 1 second
      this.pollingInterval = setInterval(async () => {
        try {
          const response = await fetch(`/api/v1/project-imports/${this.importId}`, {
            method: 'GET',
            credentials: 'include'
          });

          const data = await response.json();

          if (response.ok) {
            this.importData = data;
            this.totalRows = data.totalRows || 0;
            this.processedRows = data.processedRows || 0;
            

            // If import is completed or failed, stop polling
            if (data.status === 'completed' || data.status === 'failed') {
              
              // Stop polling and update state
              clearInterval(this.pollingInterval);
              this.pollingInterval = null;
              this.isProcessing = false;
              
              // Calculate how long the import took
              const importDuration = Date.now() - this.importStartTime;
              
              // If the import completed very quickly (less than 2 seconds) and we haven't shown
              // the "import started" notification, just show the completion notification
              if (importDuration < 2000) {
                // Cancel the timeout for the "import started" notification
                this.importStartedNotificationShown = true;
              }
              
              // Show completion message
              this.$store.dispatch('notifications/add', {
                type: data.status === 'completed' ? 'success' : 'error',
                message: data.status === 'completed' 
                  ? 'Der Import wurde erfolgreich abgeschlossen.' 
                  : 'Der Import wurde mit Fehlern abgeschlossen.'
              });
            }
          } else {
            console.error('Error in polling response:', data);
            
            // Stop polling on error
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
            this.isProcessing = false;
            
            this.$store.dispatch('notifications/add', {
              type: 'error',
              message: 'Beim Abrufen des Import-Status ist ein Fehler aufgetreten.'
            });
          }
        } catch (error) {
          console.error('Error polling for import status:', error);
          
          // Stop polling on error
          clearInterval(this.pollingInterval);
          this.pollingInterval = null;
          this.isProcessing = false;
          
          this.$store.dispatch('notifications/add', {
            type: 'error',
            message: 'Beim Abrufen des Import-Status ist ein Fehler aufgetreten.'
          });
        }
      }, 1000);
    },
    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleString();
    },
    getStatusLabel(status) {
      switch (status) {
        case 'pending':
          return 'Ausstehend';
        case 'processing':
          return 'In Bearbeitung';
        case 'completed':
          return 'Abgeschlossen';
        case 'failed':
          return 'Fehlgeschlagen';
        default:
          return status;
      }
    },
    getItemStatusLabel(status) {
      switch (status) {
        case 'valid':
          return 'Gültig';
        case 'warning':
          return 'Warnung';
        case 'error':
          return 'Fehler';
        default:
          return status;
      }
    },
    truncateText(text, maxLength) {
      if (!text) return '';
      if (text.length <= maxLength) return text;
      return text.substring(0, maxLength) + '...';
    },
    showRowDetails(row) {
      // Make sure we have a valid row object
      if (!row) {
        console.error('No row data provided to showRowDetails');
        return;
      }

      // Ensure _rawData exists
      if (!row._rawData) {
        row._rawData = row.payload || {};
      }

      // Ensure description is available in both places for consistency
      if (row.description && !row._rawData.description) {
        row._rawData.description = row.description;
      } else if (row._rawData.description && !row.description) {
        row.description = row._rawData.description;
      } else if (row.payload && row.payload.description && !row.description) {
        row.description = row.payload.description;
        row._rawData.description = row.payload.description;
      }

      // Ensure LE-Category information is available
      if (!row.leCategory && row.payload && row.payload.leFundingCategoryName) {
        row.leCategory = row.payload.leFundingCategoryName;
      } else if (!row.leCategory && row.payload && row.payload.leFundingCategoryId) {
        row.leCategory = `Kategorie ID: ${row.payload.leFundingCategoryId}`;
      } else if (!row.leCategory) {
        row.leCategory = '-';
      }

      // Ensure LocalWorkgroup information is available
      if (!row.localWorkgroup && row.payload && row.payload.localWorkgroupName) {
        row.localWorkgroup = row.payload.localWorkgroupName;
      } else if (!row.localWorkgroup && row.payload && row.payload.localWorkgroupId) {
        row.localWorkgroup = `Arbeitsgruppe ID: ${row.payload.localWorkgroupId}`;
      } else if (!row.localWorkgroup) {
        row.localWorkgroup = '-';
      }

      this.selectedRow = row;
      this.showRowDetailsModal = true;
      this.activeTab = 'general';
      this.showRawData = false;
    },
    toggleRawData() {
      this.showRawData = !this.showRawData;
    },
    getFinancingLabel(id) {
      const labels = {
        'costsGap': 'GAP Strategieplan',
        'costsPrivate': 'Private und Eigenmittel',
        'costsExternal': 'Andere Finanzquellen',
        'financing': 'Finanzierung'
      };
      return labels[id] || id;
    },
    async fetchLePeriods() {
      try {
        const response = await fetch('/api/v1/le-periods', {
          method: 'GET',
          credentials: 'include'
        });

        const data = await response.json();

        if (response.ok) {
          this.lePeriods = data;
          return data;
        } else {
          this.$store.dispatch('notifications/add', {
            type: 'error',
            message: data.error || 'Beim Laden der LE Periods ist ein Fehler aufgetreten.'
          });
          throw new Error(data.error || 'Beim Laden der LE Periods ist ein Fehler aufgetreten.');
        }
      } catch (error) {
        this.$store.dispatch('notifications/add', {
          type: 'error',
          message: 'Beim Laden der LE Periods ist ein Fehler aufgetreten.'
        });
        throw error;
      }
    },
    getSelectedPeriodName() {
      const period = this.lePeriods.find(p => p.id === this.selectedLePeriodId);
      return period ? period.name : 'Keine LE Period ausgewählt';
    },
    formatDetailKey(key) {
      // Map field keys to their proper labels
      const labelMap = {
        // Basic project information
        'title': 'Projektname',
        'projectCode': 'Projektcode',
        'description': 'Beschreibung',
        'caseStudy': 'Case Study',

        // Dates
        'startDate': 'Projektstart',
        'endDate': 'Projektdauer',

        // Funding and costs
        'projectCosts': 'Projektkosten',
        'fundingMethod': 'Finanzierung',
        'lePeriod': 'LE-Periode',

        // Regions and locations
        'states': 'Bundesland',
        'geographicRegions': 'Geographische Region',

        // Topics and tags
        'topics': 'Schwerpunkte',
        'tags': 'Schlagworte',

        // Workgroups
        'localWorkgroup': 'Lokale Arbeitsgruppe',
        'localWorkgroups': 'Kooperierende Arbeitsgruppen',
        'cooperationProjectAt': 'Kooperation mit LAGs aus AT',
        'cooperationProjectEu': 'Kooperation mit LAGs aus EU',

        // Financing details
        'financing': 'Finanzierung',
        'costsGap': 'GAP Strategieplan',
        'costsPrivate': 'Private und Eigenmittel',
        'costsExternal': 'Andere Finanzquellen',

        // Case study specific fields
        'exemplary': 'Was macht dieses Projekt besonders nachahmenswert?',
        'initialContext': 'Kontext',
        'initialContextGoals': 'Ziele',
        'additionalValue': 'Mehrwert durch Vernetzung',
        'additionalValueResult': 'Möglichkeiten zur Vernetzung',
        'innovations': 'Innovation',
        'integrationYoungCitizen': 'Einbeziehung junger Menschen',
        'integrationFemaleCitizen': 'Einbeziehung von Frauen',
        'integrationMinorities': 'Inklusion',
        'learningExperience': 'Die wichtigsten Lernerfahrungen',
        'transferable': 'Übertragbarkeit',
        'transferDetails': 'Details zur Übertragung dieses Projekts',
        'fundingMethodStakeholders': 'Welche Stakeholder waren entscheidend?',
        'resultsQuality': 'Ergebnisse und Wirkungen (Qualitativ)',
        'resultsQuantity': 'Ergebnisse und Wirkungen (Quantitativ)',

        // Business sectors
        'businessSectors': 'Geschäftsfelder',

        // Synergy tags
        'synergyFundTags': 'Synergien mit EU Politiken',
        'synergyGoalTags': 'Ziel Trägt zu EU Politiken bei'
      };

      // Return the mapped label if it exists, otherwise format the key as before
      if (labelMap[key]) {
        return labelMap[key];
      }

      // Default formatting for keys that don't have a specific mapping
      const formattedKey = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
      return formattedKey.charAt(0).toUpperCase() + formattedKey.slice(1);
    },
    formatDetailValue(value) {
      if (typeof value === 'object' && value !== null) {
        return JSON.stringify(value);
      } else if (typeof value === 'string') {
        return value;
      } else if (typeof value === 'number') {
        return value.toString();
      } else if (typeof value === 'boolean') {
        return value.toString();
      } else if (value instanceof Date) {
        return value.toLocaleString();
      } else {
        return 'Nicht unterstützter Datentyp';
      }
    },
    // New method to set LE Period based on importerType
    setLePeriodBasedOnImporterType() {
      if (this.importData && this.importData.importerType) {
        if (this.importData.importerType === 'legacy') {
          // For Legacy importer, use "LE 14-20" (id: 1)
          this.selectedLePeriodId = 1;
        } else {
          // For Standard and CaseStudy importers, use "GAP 23-27" (id: 3)
          this.selectedLePeriodId = 3;
        }
        
        // Log which LE Period was selected
        console.log(`Automatically selected LE Period based on importer type (${this.importData.importerType}): ID=${this.selectedLePeriodId}, Name=${this.getSelectedPeriodName()}`);
      }
    }
  }
};
</script>

<style scoped>
/* Styles moved to assets/styles/components/project-import-preview.scss */
/* Adding validation error styling */
.validation-error {
  display: flex;
  align-items: center;
  margin-top: 8px;
  color: #dc3545;
  font-size: 0.9rem;
}

.validation-error i {
  margin-right: 5px;
  font-size: 18px;
}

/* LE Period selection styling */
.import-details-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

/* Make the LE Period card take up the full width */
.le-period-card {
  grid-column: 1 / -1;
}

.import-details-card {
  background-color: #f8f9fa;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.import-details-card-header {
  background-color: #e9ecef;
  padding: 12px 15px;
  display: flex;
  align-items: center;
}

.import-details-card-header i {
  margin-right: 8px;
  color: #495057;
}

.import-details-card-header h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #495057;
}

.import-details-card-body {
  padding: 15px;
}

.le-period-selection .le-period-info {
  display: flex;
  align-items: flex-start;
  margin-bottom: 15px;
  background-color: #e7f1ff;
  padding: 10px;
  border-radius: 4px;
}

.le-period-selection .le-period-info .info-icon {
  color: #0d6efd;
  margin-right: 10px;
  font-size: 20px;
}

.le-period-selection .le-period-info p {
  margin: 0;
  font-size: 0.9rem;
  color: #495057;
}

.le-period-selection .form-group label {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
  font-weight: 500;
}

.le-period-selection .form-group label i {
  margin-right: 5px;
  color: #6c757d;
}

.le-period-selection .select-wrapper {
  position: relative;
}

.le-period-selection .select-wrapper select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23343a40' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 16px 12px;
}

.selected-period-info {
  display: flex;
  align-items: center;
  margin-top: 8px;
  color: #198754;
  font-size: 0.9rem;
}

.selected-period-info i {
  margin-right: 5px;
  font-size: 18px;
}
</style>