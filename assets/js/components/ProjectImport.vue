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
                      <th>Fortschritt</th>
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
                        <div class="progress">
                          <div
                            class="progress-bar"
                            role="progressbar"
                            :class="{
                              'bg-success': importItem.status === 'completed',
                              'bg-warning': importItem.status === 'pending' || importItem.status === 'processing',
                              'bg-danger': importItem.status === 'failed'
                            }"
                            :style="{ width: importItem.progress + '%' }"
                            :aria-valuenow="importItem.progress"
                            aria-valuemin="0"
                            aria-valuemax="100"
                          >
                            {{ importItem.progress }}%
                          </div>
                        </div>
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

              <div class="import-details-progress">
                <h4>Fortschritt</h4>
                <div class="progress">
                  <div
                    class="progress-bar"
                    role="progressbar"
                    :class="{
                      'bg-success': selectedImport.status === 'completed',
                      'bg-warning': selectedImport.status === 'pending' || selectedImport.status === 'processing',
                      'bg-danger': selectedImport.status === 'failed'
                    }"
                    :style="{ width: selectedImport.progress + '%' }"
                    :aria-valuenow="selectedImport.progress"
                    aria-valuemin="0"
                    aria-valuemax="100"
                  >
                    {{ selectedImport.progress }}%
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
        console.log('uploadFile: Starting upload with importerType', this.importerType);
        const formData = new FormData();
        formData.append('file', this.file);
        formData.append('importerType', this.importerType);

        console.log('uploadFile: Sending API request');
        const response = await fetch('/api/v1/project-imports', {
          method: 'POST',
          body: formData,
          credentials: 'include'
        });

        console.log('uploadFile: Received response', response.status, response.statusText);
        const data = await response.json();
        console.log('uploadFile: Response data', data);

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
          
          console.error('Upload error:', data);
          
          this.setStatusMessage(errorMessage, 'error', 'error');
        }
      } catch (error) {
        console.error('Upload exception:', error);
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

<style scoped>
.project-import-component {
  padding: 20px;
}

.project-import-component-title {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.project-import-component-title h2 {
  margin-right: auto;
  margin-bottom: 0;
}

.project-import-component-title-actions {
  display: flex;
  gap: 10px;
}

.card {
  background-color: #fff;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;
  overflow: hidden;
}

.card-header {
  background-color: #f6f6f6;
  padding: 15px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  margin: 0;
  font-size: 1.2em;
  color: #6090d8;
}

.card-header-actions {
  display: flex;
  gap: 10px;
}

.card-body {
  padding: 20px;
}

.import-instructions {
  margin-bottom: 20px;
}

.import-steps {
  list-style: none;
  padding: 0;
  margin: 15px 0;
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
}

.import-steps li {
  display: flex;
  align-items: center;
  background-color: #f6f6f6;
  padding: 10px 15px;
  border-radius: 4px;
  font-weight: bold;
}

.step-number {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  background-color: #6090d8;
  color: white;
  border-radius: 50%;
  margin-right: 10px;
  font-size: 0.9em;
}

.import-form {
  background-color: #f6f6f6;
  padding: 20px;
  border-radius: 4px;
}

.file-input-wrapper {
  position: relative;
}

.selected-file {
  display: flex;
  align-items: center;
  margin-top: 10px;
  padding: 10px;
  background-color: #e9ecef;
  border-radius: 4px;
}

.selected-file i {
  margin-right: 10px;
  color: #6090d8;
}

.import-type-detection {
  display: flex;
  align-items: center;
  margin-top: 10px;
  padding: 10px;
  background-color: #e9ecef;
  border-radius: 4px;
  border-left: 4px solid #6090d8;
}

.import-type-detection i {
  margin-right: 10px;
  color: #6090d8;
}

.import-type-detection span {
  font-weight: bold;
}

.badge {
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: bold;
  display: inline-flex;
  align-items: center;
}

.badge-success {
  background-color: #28a745;
  color: white;
}

.badge-warning {
  background-color: #ffc107;
  color: black;
}

.badge-danger {
  background-color: #dc3545;
  color: white;
}

.badge-info {
  background-color: #17a2b8;
  color: white;
}

.progress {
  height: 20px;
  background-color: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.progress-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #007bff;
  color: white;
  transition: width 0.3s ease;
}

.progress-bar-striped {
  background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
  background-size: 1rem 1rem;
}

.progress-bar-animated {
  animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
  from { background-position: 1rem 0; }
  to { background-position: 0 0; }
}

.processing-status {
  background-color: #f8f9fa;
  padding: 20px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.processing-status-header {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
}

.processing-status-header h4 {
  margin: 0;
  margin-left: 10px;
  color: #6090d8;
}

.processing-icon {
  color: #6090d8;
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.bg-success {
  background-color: #28a745 !important;
}

.bg-warning {
  background-color: #ffc107 !important;
}

.bg-danger {
  background-color: #dc3545 !important;
}

.bg-info {
  background-color: #17a2b8 !important;
}

.import-filename, .import-date {
  display: flex;
  align-items: center;
}

.import-filename i, .import-date i {
  margin-right: 8px;
  color: #6090d8;
}

.action-buttons {
  display: flex;
  gap: 5px;
}

.button.small {
  padding: 5px 10px;
  font-size: 12px;
}

.button.danger {
  background-color: #dc3545;
  color: white;
}

.button.danger:hover {
  background-color: #c82333;
}

.no-imports {
  text-align: center;
  padding: 30px;
  color: #6c757d;
}

.no-imports i {
  font-size: 48px;
  margin-bottom: 10px;
  color: #e9ecef;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  border-radius: 4px;
  width: 80%;
  max-width: 900px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px 20px;
  border-bottom: 1px solid #e9ecef;
}

.modal-header h3 {
  margin: 0;
  display: flex;
  align-items: center;
  color: #6090d8;
}

.modal-header h3 i {
  margin-right: 10px;
}

.modal-body {
  padding: 20px;
}

.close {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 24px;
  color: #6c757d;
}

.close:hover {
  color: #dc3545;
}

.import-details-summary {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.import-details-card {
  background-color: #f8f9fa;
  border-radius: 4px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.import-details-card-header {
  background-color: #e9ecef;
  padding: 10px 15px;
  display: flex;
  align-items: center;
}

.import-details-card-header i {
  margin-right: 10px;
  color: #6090d8;
}

.import-details-card-header h4 {
  margin: 0;
  font-size: 1em;
}

.import-details-card-body {
  padding: 15px;
}

.detail-item {
  margin-bottom: 8px;
  display: flex;
  align-items: baseline;
}

.detail-label {
  font-weight: bold;
  color: #6c757d;
  width: 150px;
  flex-shrink: 0;
}

.detail-value {
  flex-grow: 1;
}

.import-details-progress {
  margin-bottom: 20px;
}

.import-details-progress h4,
.import-details-error h4,
.import-details-items h4 {
  margin-top: 0;
  margin-bottom: 10px;
  color: #6090d8;
  font-size: 1.1em;
}

.error-message {
  background-color: #f8d7da;
  color: #721c24;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.error-message pre {
  margin: 0;
  white-space: pre-wrap;
}

.error-message-cell {
  color: #dc3545;
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.table-responsive {
  overflow-x: auto;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #e9ecef;
}

.modal-actions .button {
  display: flex;
  align-items: center;
}

.modal-actions .button i {
  margin-right: 5px;
}

.item-content {
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.detected-type-info {
  margin-top: 10px;
  padding: 8px 12px;
  background-color: #e8f5e9;
  color: #2e7d32;
  border-radius: 4px;
  display: flex;
  align-items: center;
  border: 1px solid #c8e6c9;
}

.detected-type-info i {
  margin-right: 8px;
  color: #2e7d32;
}

.status-message {
  padding: 10px 15px;
  border-radius: 4px;
  margin-top: 15px;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  font-size: 0.95rem;
}

.status-message i {
  margin-right: 10px;
  font-size: 20px;
}

.status-message.error {
  background-color: #ffebee;
  color: #d32f2f;
  border: 1px solid #ffcdd2;
}

.status-message.success {
  background-color: #e8f5e9;
  color: #2e7d32;
  border: 1px solid #c8e6c9;
}

.status-message.info {
  background-color: #e3f2fd;
  color: #1976d2;
  border: 1px solid #bbdefb;
}

.import-type-badge {
  font-size: 0.9rem;
  padding: 5px 10px;
  border-radius: 4px;
  font-weight: 500;
  display: inline-block;
}

/* Type-specific styling */
.import-type-legacy {
  background-color: #6c757d;
  color: white;
}

.import-type-standard {
  background-color: #007bff;
  color: white;
}

.import-type-casestudy {
  background-color: #28a745;
  color: white;
}
</style> 