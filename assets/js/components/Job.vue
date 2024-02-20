<template>

    <div class="job-component">

        <div class="job-component-form">

            <div class="job-component-form-header">

                <h3>Eintrag erstellen</h3>

                <div class="job-component-form-header-actions">
                    <a class="button" @click="showPreview = true">Vorschau</a>
                    <a class="button warning" @click="job.isPublic = true" v-if="!job.isPublic">Entwurf</a>
                    <a class="button success" @click="job.isPublic = false" v-if="job.isPublic">Öffentlich</a>
                    <a @click="locale = 'de'" class="button" :class="{primary: locale === 'de'}">DE</a>
                    <a @click="locale = 'fr'" class="button" :class="{primary: locale === 'fr'}">FR</a>
                    <a @click="locale = 'it'" class="button" :class="{primary: locale === 'it'}">IT</a>
                    <a class="button error" @click="clickDelete()" v-if="job.id">Löschen</a>
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="job-component-form-section">

                <div class="row">
                    <div class="col-md-6" v-if="locale === 'de'">
                        <label for="title">Bezeichnung</label>
                        <input id="title" type="text" class="form-control" v-model="job.name" :placeholder="translate('name', job)">
                    </div>
                    <div class="col-md-6" v-else>
                        <label for="title">Bezeichnung (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="title" type="text" class="form-control" v-model="job.translations[locale].name" :placeholder="translate('name', job)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="description">Text</label>
                        <ckeditor id="description" :editor="editor" :config="editorConfig"
                                  v-model="job.description" :placeholder="translate('description', job)"></ckeditor>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="description">Text (Übersetzung {{ locale.toUpperCase() }})</label>
                        <ckeditor id="description" :editor="editor" :config="editorConfig"
                                  v-model="job.translations[locale].description" :placeholder="translate('description', job)"></ckeditor>
                    </div>
                </div>

                <div class="job-component-form-section-group">

                    <div class="job-component-form-section-group-headline">Kategorisierung</div>

                    <div class="row">
                        <div class="col-md-3">
                            <label for="locations">Arbeitsort</label>
                            <tag-selector id="locations" :model="job.locations"
                                          :options="locations.filter(location => !location.context || location.context === 'job')" :searchType="'select'"></tag-selector>
                        </div>
                        <div class="col-md-3">
                            <label for="stints">Pensum</label>
                            <tag-selector id="stints" :model="job.stints"
                                          :options="stints.filter(stint => !stint.context || stint.context === 'job')" :searchType="'select'"></tag-selector>
                        </div>
                    </div>

                </div>

                <div class="job-component-form-section-group">

                    <div class="job-component-form-section-group-headline">Kontaktdetails</div>

                    <div class="row">
                        <div class="col-md-4" v-if="locale === 'de'">
                            <label for="location">Ort</label>
                            <input id="location" type="text" class="form-control" v-model="job.location" :placeholder="translate('location', job)">
                        </div>
                        <div class="col-md-4" v-else>
                            <label for="location">Ort (Übersetzung {{ locale.toUpperCase() }})</label>
                            <input id="location" type="text" class="form-control" v-model="job.translations[locale].location" :placeholder="translate('location', job)">
                        </div>
                        <div class="col-md-4" v-if="locale === 'de'">
                            <label for="employer">Arbeitgeber</label>
                            <input id="employer" type="text" class="form-control" v-model="job.employer" :placeholder="translate('employer', job)">
                        </div>
                        <div class="col-md-4" v-else>
                            <label for="employer">Arbeitgeber (Übersetzung {{ locale.toUpperCase() }})</label>
                            <input id="employer" type="text" class="form-control" v-model="job.translations[locale].employer" :placeholder="translate('employer', job)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4" v-if="locale === 'de'">
                            <label for="contact">Kontakt</label>
                            <textarea name="contact" id="contact" class="form-control" rows="3" v-model="job.contact" :placeholder="translate('contact', job)"></textarea>
                        </div>
                        <div class="col-md-4" v-else>
                            <label for="contact">Kontakt (Übersetzung {{ locale.toUpperCase() }})</label>
                            <textarea name="contact" id="contact" class="form-control" rows="3" v-model="job.translations[locale].contact" :placeholder="translate('contact', job)"></textarea>
                        </div>
                    </div>

                </div>

                <div class="job-component-form-section-group">

                    <div class="job-component-form-section-group-headline">Termindetails</div>

                    <div class="row">
                        <div class="col-md-2">
                            <label for="applicationDeadline">Bewerbungsfrist</label>
                            <date-picker mode="dateTime" :is24hr="true" v-model="job.applicationDeadline" :locale="'de'">
                                <template v-slot="{ inputValue, inputEvents }">
                                    <input type="text" class="form-control"
                                           :value="inputValue"
                                           v-on="inputEvents"
                                           id="applicationDeadline">
                                </template>
                            </date-picker>
                        </div>
                    </div>

                </div>

                <div class="job-component-form-section-group">

                    <div class="job-component-form-section-group-headline">Weiterführende Informationen</div>

                    <div class="row">
                        <div class="col-md-8">
                            <label v-if="locale === 'de'">Links</label>
                            <label v-else>Links (Übersetzung {{ locale.toUpperCase() }})</label>
                            <div class="row" v-for="(link, index) in (locale === 'de' ? job.links : job.translations[locale].links)">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" v-model="link.label" placeholder="Bezeichnung">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" v-model="link.value" placeholder="URL">
                                </div>
                                <div class="col-md-3">
                                    <button class="button error" @click="clickRemoveLink(index)">Link entfernen</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <button class="button success" @click="clickAddLink()">Link hinzufügen</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="files">Dokumente</label>
                            <file-selector id="files" :items="job.files" :locale="locale" @changed="updateFiles"></file-selector>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="job-component-overlay" v-if="showPreview" @click="showPreview = false">

            <EmbedJobsView @click.stop @clickClose="showPreview = false"
                           :job="job" :locale="locale"></EmbedJobsView>

        </div>

        <transition name="fade">
            <Modal v-if="modal" :config="modal"></Modal>
        </transition>

    </div>

</template>

<script>
import { mapState } from 'vuex';
import draggable from 'vuedraggable';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import TagSelector from './TagSelector';
import FileSelector from './FileSelector';
import { DatePicker } from 'v-calendar';
import Modal from './Modal';
import EmbedJobsView from './EmbedJobsView.vue';

export default {
    data() {
        return {
            locale: 'de',
            job: {
                isPublic: false,
                position: 10000,
                name: '',
                description: '',
                employer: '',
                location: '',
                contact: '',
                applicationDeadline: null,
                locations: [],
                stints: [],
                links: [],
                files: [],
                translations: {
                    fr: {
                        links: [],
                    },
                    it: {
                        links: [],
                    },
                },
            },
            showPreview: false,
            modal: null,
            editor: ClassicEditor,
            editorConfig: {
                basicEntities: false,
                toolbar: {
                    items: [
                        'heading',
                        '|',
                        'bold',
                        'italic',
                        'link',
                        '|',
                        'numberedList',
                        'bulletedList',
                        'insertTable',
                        '|',
                        'undo',
                        'redo',
                    ]
                }
            },
            simpleEditorConfig: {
                basicEntities: false,
                toolbar: {
                    items: [
                        'bold',
                        'italic',
                        'link',
                        '|',
                        'numberedList',
                        'bulletedList',
                        'insertTable',
                    ]
                }
            },
        };
    },
    components: {
        TagSelector,
        FileSelector,
        DatePicker,
        draggable,
        Modal,
        EmbedJobsView,
    },
    computed: {
        ...mapState({
            selectedJob: state => state.jobs.job,
            languages: state => state.languages.all,
            locations: state => state.locations.all,
            stints: state => state.stints.all,
        }),
    },
    methods: {
        clickDelete () {
            this.modal = {
                title: 'Eintrag löschen',
                description: 'Sind Sie sicher dass Sie diesen Eintrag unwiderruflich löschen möchten?',
                actions: [
                    {
                        label: 'Endgültig löschen',
                        class: 'error',
                        onClick: () => {
                            this.$store.dispatch('jobs/delete', this.job.id).then(() => {
                                this.$router.push('/jobs');
                            });
                        }
                    },
                    {
                        label: 'Abbrechen',
                        class: 'warning',
                        onClick: () => {
                            this.modal = null;
                        }
                    }
                ],
            };
        },
        clickCancel () {
            this.$router.push('/jobs');
        },
        clickSave() {

            if(!this.job.applicationDeadline) {
                this.job.applicationDeadline = null;
            }

            if(this.job.id) {
                return this.$store.dispatch('jobs/update', this.job).then(() => {
                    this.$router.push('/jobs');
                });
            }

            this.$store.dispatch('jobs/create', this.job).then(() => {
                this.$router.push('/jobs');
            });

        },
        reload() {
            if(this.$route.params.id) {
                this.$store.commit('jobs/set', {});
                this.$store.dispatch('jobs/load', this.$route.params.id).then(() => {
                    this.job = {...this.selectedJob};
                });
            }
        },
        clickRemoveProgram(programIndex) {
            if(this.job.programs[programIndex].units.length) {
                return this.modal = {
                    title: 'Programm entfernen',
                    description: 'Sind Sie sicher dass Sie diesen Eintrag unwiderruflich entfernen möchten?',
                    actions: [
                        {
                            label: 'Entfernen',
                            class: 'error',
                            onClick: () => {
                                this.job.programs.splice(programIndex, 1);
                                this.modal = null;
                            }
                        },
                        {
                            label: 'Abbrechen',
                            class: 'warning',
                            onClick: () => {
                                this.modal = null;
                            }
                        }
                    ],
                };
            }

            this.job.programs.splice(programIndex, 1);
        },
        clickAddUnit(programIndex) {
            this.job.programs[programIndex].units.push({
                time: '',
                descriptions: [{
                    value: '',
                    attachments: [],
                    translations: {
                        'fr': {
                            value: '',
                        },
                        'it': {
                            value: '',
                        },
                    },
                }],
                translations: {
                    'fr': {
                        time: '',
                    },
                    'it': {
                        time: '',
                    },
                },
            });
        },
        clickAddUnitDescription(programIndex, unitIndex) {
            this.job.programs[programIndex].units[unitIndex].descriptions.push({
                value: '',
                attachments: [],
                translations: {
                    'fr': {
                        value: '',
                    },
                    'it': {
                        value: '',
                    },
                },
            });
        },
        clickRemoveUnitDescription(programIndex, unitIndex, descriptionIndex) {
            let unit = this.job.programs[programIndex].units[unitIndex].descriptions.splice(descriptionIndex, 1)[0];
        },
        clickMoveUpUnit(programIndex, unitIndex) {
            let unit = this.job.programs[programIndex].units.splice(unitIndex, 1)[0];
            this.job.programs[programIndex].units.splice(unitIndex-1, 0, unit);
        },
        clickMoveDownUnit(programIndex, unitIndex) {
            let unit = this.job.programs[programIndex].units.splice(unitIndex, 1)[0];
            this.job.programs[programIndex].units.splice(unitIndex+1, 0, unit);
        },
        clickRemoveUnit(programIndex, unitIndex) {
            let unit = this.job.programs[programIndex].units.splice(unitIndex, 1)[0];
        },
        clickAddLink() {
            (this.locale === 'de' ? this.job.links : this.job.translations[this.locale].links).push({
                value: '',
                label: '',
            });
        },
        clickRemoveLink(index) {
            let link = (this.locale === 'de' ? this.job.links : this.job.translations[this.locale].links).splice(index, 1)[0];
        },
        updateFiles(files) {
            this.job.files = files;
        },
        translate(property, context) {
            if(this.locale === 'de') {
                return context[property] || context.translations.fr[property] || context.translations.it[property];
            }
            if(this.locale === 'fr') {
                return context.translations.fr[property] || context[property] || context.translations.it[property];
            }
            if(this.locale === 'it') {
                return context.translations.it[property] || context.translations.fr[property] || context[property];
            }
            return context[property];
        },
        getPreviewLink() {
            let url = 'https://regiosuisse.ch';

            if(this.locale === 'fr') {
                url += '/fr';
            }

            if(this.locale === 'it') {
                url += '/it';
            }

            return url + '/agenda?job-id=' + this.job.id;
        },
        parseYoutubeId(url) {
            const result = (url || '').split(/(vi\/|v=|\/v\/|youtu\.be\/|\/embed\/)/);
            return (result[2] !== undefined) ? result[2].split(/[^0-9a-z_\-]/i)[0] : false;
        },
    },
    created () {
        this.reload();
    }
}
</script>