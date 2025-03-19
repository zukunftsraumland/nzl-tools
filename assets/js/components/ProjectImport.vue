<template>
  <div class="project-import-component">
    <div class="project-import-component-title">
      <h2>Projektimport</h2>

      <transition name="fade" mode="out-in">
        <div class="loading-indicator" v-if="isLoading('projectImport')"></div>
      </transition>

      <div class="project-import-component-title-actions">
        <router-link :to="'/projects'" class="button">Zurück zur Liste</router-link>
      </div>
    </div>

    <div class="project-import-component-content">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3>Neue Projekte importieren</h3>
            </div>
            <div class="card-body">
              <div class="import-instructions">
                <p>
                  Laden Sie eine Excel-Datei hoch, um neue Projekte zu importieren.
                  Die Datei sollte eine Kopfzeile mit den Spaltennamen enthalten.
                </p>
                <ul class="import-steps">
                  <li><span class="step-number">1</span> Excel-Datei auswählen</li>
                  <li><span class="step-number">2</span> Datei hochladen</li>
                  <li><span class="step-number">3</span> Vorschau prüfen und Import starten</li>
                </ul>
              </div>

              <div class="import-form">
                <div class="form-group">
                  <label for="file">
                    <i class="material-icons">upload_file</i>
                    Excel-Datei auswählen
                  </label>
                  <div class="file-input-wrapper">
                    <input
                      type="file"
                      id="file"
                      ref="file"
                      class="form-control"
                      @change="handleFileUpload"
                      accept=".xlsx,.xls"
                    />
                    <div class="selected-file" v-if="file">
                      <i class="material-icons">description</i>
                      <span>{{ file.name }}</span>
                    </div>
                    
                    <div class="import-type-detection" v-if="file">
                      <i class="material-icons">auto_awesome</i>
                      <span>Der Import-Typ wird automatisch erkannt</span>
                    </div>
                  </div>
                </div>

                <div class="form-group" v-if="false">
                  <label for="importerType">
                    <i class="material-icons">category</i>
                    Import-Typ auswählen
                  </label>
                  <select
                    id="importerType"
                    v-model="importerType"
                    class="form-control"
                  >
                    <option v-for="importer in importers" :key="importer.type" :value="importer.type">
                      {{ importer.name }} - {{ importer.description }}
                    </option>
                  </select>
                </div>

                <div class="form-group">
                  <button
                    class="button primary"
                    @click="uploadFile"
                    :disabled="!file || isUploading"
                  >
                    <i class="material-icons">cloud_upload</i>
                    {{ isUploading ? 'Wird hochgeladen...' : 'Hochladen' }}
                  </button>
                </div>
                
                <div class="form-group" v-if="isUploading">
                  <div class="processing-status">
                    <div class="processing-status-header">
                      <i class="material-icons processing-icon">sync</i>
                      <h4>Datei wird hochgeladen und vorbereitet</h4>
                    </div>
                    <div v-if="detectedImporterTypeName" class="detected-type-info">
                      <i class="material-icons">check_circle</i>
                      <span>Erkannter Datei-Typ: <strong>{{ detectedImporterTypeName }}</strong></span>
                    </div>
                  </div>
                </div>
                
                <div v-if="statusMessage" class="status-message" :class="statusMessageType">
                  <i class="material-icons">{{ statusMessageIcon }}</i>
                  <span>{{ statusMessage }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-4" v-if="imports.length > 0">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3>Import-Verlauf</h3>
              <div class="card-header-actions">
                <button class="button small" @click="fetchImports" title="Aktualisieren">
                  <i class="material-icons">refresh</i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Dateiname</th>
                      <th>Status</th>
                      <th>Import-Typ</th>
                      <th>Erstellt am</th>
                      <th>Aktionen</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="importItem in imports" :key="importItem.id" 
                        :class="{
                          'success': importItem.status === 'completed',
                          'warning': importItem.status === 'pending' || importItem.status === 'processing',
                          'error': importItem.status === 'failed'
                        }">
                      <td>{{ importItem.id }}</td>
                      <td>
                        <div class="import-filename">
                          <i class="material-icons">description</i>
                          <span>{{ importItem.originalFilename }}</span>
                        </div>
                      </td>
                      <td>
                        <span
                          :class="{
                            'badge-success': importItem.status === 'completed',
                            'badge-warning': importItem.status === 'pending' || importItem.status === 'processing',
                            'badge-danger': importItem.status === 'failed'
                          }"
                          class="badge"
                        >
                          {{ getStatusLabel(importItem.status) }}
                        </span>
                      </td>
                      <td>
                        <span v-if="importItem.importerType" class="badge import-type-badge" :class="'import-type-' + importItem.importerType">
                          {{ getImporterTypeLabel(importItem.importerType) }}
                        </span>
                      </td>
                    
                      <td>
                        <div class="import-date">
                          <i class="material-icons">event</i>
                          <span>{{ formatDate(importItem.createdAt) }}</span>
                        </div>
                      </td>
                      <td>
                        <div class="action-buttons">
                          <button
                            class="button small"
                            @click="viewImport(importItem)"
                            title="Details anzeigen"
                          >
                            <i class="material-icons">visibility</i>
                          </button>
                          <button
                            v-if="importItem.status === 'pending'"
                            class="button small primary"
                            @click="previewImport(importItem)"
                            title="Vorschau anzeigen und Import starten"
                          >
                            <i class="material-icons">preview</i>
                          </button>
                          <button
                            class="button small danger"
                            @click="deleteImport(importItem)"
                            title="Löschen"
                          >
                            <i class="material-icons">delete</i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <div class="no-imports" v-if="imports.length === 0">
                <p>Keine Importe vorhanden.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-4" v-else>
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3>Import-Verlauf</h3>
            </div>
            <div class="card-body">
              <div class="no-imports">
                <i class="material-icons">inbox</i>
                <p>Keine Importe vorhanden.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Import Details Modal -->
      <div class="modal" v-if="showImportDetails">
        <div class="modal-content">
          <div class="modal-header">
            <h3>
              <i class="material-icons">info</i>
              Import Details
            </h3>
            <button class="close" @click="showImportDetails = false">
              <i class="material-icons">close</i>
            </button>
          </div>
          <div class="modal-body">
            <div v-if="selectedImport">
              <div class="import-details-summary">
                <div class="import-details-card">
                  <div class="import-details-card-header">
                    <i class="material-icons">description</i>
                    <h4>Allgemeine Informationen</h4>
                  </div>
                  <div class="import-details-card-body">
                    <div class="detail-item">
                      <span class="detail-label">ID:</span>
                      <span class="detail-value">{{ selectedImport.id }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Dateiname:</span>
                      <span class="detail-value">{{ selectedImport.originalFilename }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Status:</span>
                      <span class="detail-value">
                        <span
                          :class="{
                            'badge-success': selectedImport.status === 'completed',
                            'badge-warning': selectedImport.status === 'pending' || selectedImport.status === 'processing',
                            'badge-danger': selectedImport.status === 'failed'
                          }"
                          class="badge"
                        >
                          {{ getStatusLabel(selectedImport.status) }}
                        </span>
                      </span>
                    </div>
                    <div class="detail-item" v-if="selectedImport.importerType">
                      <span class="detail-label">Import-Typ:</span>
                      <span class="detail-value">
                        <span class="badge import-type-badge" :class="'import-type-' + selectedImport.importerType">
                          {{ getImporterTypeLabel(selectedImport.importerType) }}
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
                      <span class="detail-value">{{ formatDate(selectedImport.createdAt) }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Aktualisiert am:</span>
                      <span class="detail-value">{{ formatDate(selectedImport.updatedAt) }}</span>
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
                      <span class="detail-value">{{ selectedImport.totalRows }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Zeilen verarbeitet:</span>
                      <span class="detail-value">{{ selectedImport.processedRows }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Zeilen erfolgreich:</span>
                      <span class="detail-value">{{ selectedImport.successfulRows }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Zeilen mit Fehlern:</span>
                      <span class="detail-value">{{ selectedImport.errorRows }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="import-details-error" v-if="selectedImport.errorMessage">
                <h4>Fehlermeldung</h4>
                <div class="error-message">
                  <pre>{{ selectedImport.errorMessage }}</pre>
                </div>
              </div>
              
              <div class="modal-actions">
                <button class="button primary" @click="showImportDetails = false">Schließen</button>
                <button 
                  v-if="selectedImport.status === 'pending'"
                  class="button" 
                  @click="previewImport(selectedImport)"
                >
                  <i class="material-icons">preview</i>
                  Vorschau anzeigen
                </button>
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
      file: null,
      isUploading: false,
      imports: [],
      showImportDetails: false,
      selectedImport: null,
      importers: [],
      importerType: 'standard',
      detectedImporterTypeName: null,
      statusMessage: '',
      statusMessageType: '',
      statusMessageIcon: ''
    };
  },
  created() {
    this.fetchImports();
    this.fetchImporters();
  },
  methods: {
    isLoading(key) {
      return this.$store.getters['loaders/isLoading'](key);
    },
    handleFileUpload() {
      this.file = this.$refs.file.files[0];
    },
    async fetchImporters() {
      this.$store.commit('loaders/showLoader', 'projectImport');

      try {
        const response = await fetch('/api/v1/project-imports/importers', {
          method: 'GET',
          credentials: 'include'
        });

        const data = await response.json();

        if (response.ok) {
          this.importers = data;
          if (this.importers.length > 0) {
            this.importerType = this.importers[0].type;
          }
        } else {
          this.setStatusMessage(data.error || 'Beim Laden der Importtypen ist ein Fehler aufgetreten.', 'error', 'error');
        }
      } catch (error) {
        this.setStatusMessage('Beim Laden der Importtypen ist ein Fehler aufgetreten.', 'error', 'error');
      } finally {
        this.$store.commit('loaders/hideLoader', 'projectImport');
      }
    },
    async uploadFile() {
      if (!this.file) {
        this.setStatusMessage('Bitte wählen Sie eine Datei aus.', 'error', 'error');
        return;
      }

      this.isUploading = true;
      this.$store.commit('loaders/showLoader', 'projectImport');
      
      this.setStatusMessage('Die Datei wird hochgeladen und vorbereitet...', 'info', 'info');

      try {
        const formData = new FormData();
        formData.append('file', this.file);
        formData.append('importerType', this.importerType);

        const response = await fetch('/api/v1/project-imports', {
          method: 'POST',
          body: formData,
          credentials: 'include'
        });

        const data = await response.json();

        if (response.ok) {
          this.setStatusMessage('Die Datei wurde erfolgreich hochgeladen und vorbereitet.', 'success', 'check_circle');
          
          this.file = null;
          this.$refs.file.value = '';
          this.fetchImports();
          
          if (data && data.importerTypeName) {
            this.detectedImporterTypeName = data.importerTypeName;
          }
          
          if (data && data.id) {
            this.setStatusMessage('Sie werden zur Vorschauseite weitergeleitet...', 'info', 'info');
            
            this.previewImport(data);
          }
        } else {
          let errorMessage = 'Beim Hochladen der Datei ist ein Fehler aufgetreten.';
          
          if (data.error) {
            errorMessage = data.error;
            
            if (data.message) {
              errorMessage += ': ' + data.message;
            }
          }
          
          
          this.setStatusMessage(errorMessage, 'error', 'error');
        }
      } catch (error) {
        this.setStatusMessage('Beim Hochladen der Datei ist ein Fehler aufgetreten: ' + (error.message || 'Unbekannter Fehler'), 'error', 'error');
      } finally {
        this.isUploading = false;
        this.$store.commit('loaders/hideLoader', 'projectImport');
      }
    },
    async fetchImports() {
      this.$store.commit('loaders/showLoader', 'projectImport');

      try {
        const response = await fetch('/api/v1/project-imports', {
          method: 'GET',
          credentials: 'include'
        });

        const data = await response.json();

        if (response.ok) {
          this.imports = data;
          this.clearStatusMessage();
        } else {
          this.setStatusMessage(data.error || 'Beim Laden der Importe ist ein Fehler aufgetreten.', 'error', 'error');
        }
      } catch (error) {
        this.setStatusMessage('Beim Laden der Importe ist ein Fehler aufgetreten.', 'error', 'error');
      } finally {
        this.$store.commit('loaders/hideLoader', 'projectImport');
      }
    },
    async viewImport(importItem) {
      this.$store.commit('loaders/showLoader', 'projectImport');

      try {
        const response = await fetch(`/api/v1/project-imports/${importItem.id}`, {
          method: 'GET',
          credentials: 'include'
        });

        const data = await response.json();

        if (response.ok) {
          this.selectedImport = data;
          this.showImportDetails = true;
          this.clearStatusMessage();
        } else {
          this.setStatusMessage(data.error || 'Beim Laden der Import-Details ist ein Fehler aufgetreten.', 'error', 'error');
        }
      } catch (error) {
        this.setStatusMessage('Beim Laden der Import-Details ist ein Fehler aufgetreten.', 'error', 'error');
      } finally {
        this.$store.commit('loaders/hideLoader', 'projectImport');
      }
    },
    previewImport(importItem) {
      this.$router.push({ name: 'project-import-preview', params: { id: importItem.id } });
    },
    async deleteImport(importItem) {
      if (!confirm('Sind Sie sicher, dass Sie diesen Import löschen möchten?')) {
        return;
      }

      this.$store.commit('loaders/showLoader', 'projectImport');

      try {
        const response = await fetch(`/api/v1/project-imports/${importItem.id}`, {
          method: 'DELETE',
          credentials: 'include'
        });

        const data = await response.json();

        if (response.ok) {
          this.setStatusMessage('Der Import wurde erfolgreich gelöscht.', 'success', 'check_circle');
          this.fetchImports();
        } else {
          this.setStatusMessage(data.error || 'Beim Löschen des Imports ist ein Fehler aufgetreten.', 'error', 'error');
        }
      } catch (error) {
        this.setStatusMessage('Beim Löschen des Imports ist ein Fehler aufgetreten.', 'error', 'error');
      } finally {
        this.$store.commit('loaders/hideLoader', 'projectImport');
      }
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
        case 'skipped':
          return 'Übersprungen';
        default:
          return status;
      }
    },
    setStatusMessage(message, type, icon) {
      this.statusMessage = message;
      this.statusMessageType = type;
      this.statusMessageIcon = icon;
    },
    clearStatusMessage() {
      this.statusMessage = '';
      this.statusMessageType = '';
      this.statusMessageIcon = '';
    },
    getImporterTypeLabel(type) {
      const importer = this.importers.find(i => i.type === type);
      return importer ? importer.name : type;
    }
  }
};
</script>
