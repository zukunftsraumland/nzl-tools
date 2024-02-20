<template>

    <div class="education-component">

        <div class="education-component-form">

            <div class="education-component-form-header">

                <h3>Eintrag erstellen</h3>

                <div class="education-component-form-header-actions">
                    <a class="button" @click="showPreview = true">Vorschau</a>
                    <a class="button warning" @click="education.isPublic = true" v-if="!education.isPublic">Entwurf</a>
                    <a class="button success" @click="education.isPublic = false" v-if="education.isPublic">Öffentlich</a>
                    <a @click="locale = 'de'" class="button" :class="{primary: locale === 'de'}">DE</a>
                    <a @click="locale = 'fr'" class="button" :class="{primary: locale === 'fr'}">FR</a>
                    <a @click="locale = 'it'" class="button" :class="{primary: locale === 'it'}">IT</a>
                    <a class="button error" @click="clickDelete()" v-if="education.id">Löschen</a>
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="education-component-form-section">

                <div class="row">
                    <div class="col-md-6" v-if="locale === 'de'">
                        <label for="title">Bezeichnung</label>
                        <input id="title" type="text" class="form-control" v-model="education.name" :placeholder="translate('name', education)">
                    </div>
                    <div class="col-md-6" v-else>
                        <label for="title">Bezeichnung (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="title" type="text" class="form-control" v-model="education.translations[locale].name" :placeholder="translate('name', education)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="text">Text</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="education.text" :placeholder="translate('text', education)"></ckeditor>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="text">Text (Übersetzung {{ locale.toUpperCase() }})</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="education.translations[locale].text" :placeholder="translate('text', education)"></ckeditor>
                    </div>
                </div>

                <div class="education-component-form-section-group">

                    <div class="education-component-form-section-group-headline">Kategorisierung</div>

                    <div class="row">
                        <div class="col-md-3">
                            <label for="educationTypes">Art der Weiterbildung</label>
                            <tag-selector id="educationTypes" :model="education.educationTypes"
                                          :options="educationTypes.filter(educationType => !educationType.context || educationType.context === 'education')" :searchType="'select'"></tag-selector>
                        </div>
                        <div class="col-md-3">
                            <label for="languages">Durchführungssprache</label>
                            <tag-selector id="languages" :model="education.languages"
                                          :options="languages.filter(language => !language.context || language.context === 'education')" :searchType="'select'"></tag-selector>
                        </div>
                        <div class="col-md-3">
                            <label for="locations">Durchführungsort</label>
                            <tag-selector id="locations" :model="education.locations"
                                          :options="locations.filter(location => !location.context || location.context === 'education')" :searchType="'select'"></tag-selector>
                        </div>
                    </div>

                </div>

                <div class="education-component-form-section-group">

                    <div class="education-component-form-section-group-headline">Kontaktdetails</div>

                    <div class="row">
                        <div class="col-md-4" v-if="locale === 'de'">
                            <label for="location">Veranstaltungsort</label>
                            <input id="location" type="text" class="form-control" v-model="education.location" :placeholder="translate('location', education)">
                        </div>
                        <div class="col-md-4" v-else>
                            <label for="location">Veranstaltungsort (Übersetzung {{ locale.toUpperCase() }})</label>
                            <input id="location" type="text" class="form-control" v-model="education.translations[locale].location" :placeholder="translate('location', education)">
                        </div>
                        <div class="col-md-4" v-if="locale === 'de'">
                            <label for="organizer">Veranstalter</label>
                            <input id="organizer" type="text" class="form-control" v-model="education.organizer" :placeholder="translate('organizer', education)">
                        </div>
                        <div class="col-md-4" v-else>
                            <label for="organizer">Veranstalter (Übersetzung {{ locale.toUpperCase() }})</label>
                            <input id="organizer" type="text" class="form-control" v-model="education.translations[locale].organizer" :placeholder="translate('organizer', education)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4" v-if="locale === 'de'">
                            <label for="contact">Kontakt</label>
                            <textarea name="contact" id="contact" class="form-control" rows="3" v-model="education.contact" :placeholder="translate('contact', education)"></textarea>
                        </div>
                        <div class="col-md-4" v-else>
                            <label for="contact">Kontakt (Übersetzung {{ locale.toUpperCase() }})</label>
                            <textarea name="contact" id="contact" class="form-control" rows="3" v-model="education.translations[locale].contact" :placeholder="translate('contact', education)"></textarea>
                        </div>
                    </div>

                </div>

                <div class="education-component-form-section-group">

                    <div class="education-component-form-section-group-headline">Weiterführende Informationen</div>

                    <div class="row">
                        <div class="col-md-8">
                            <label v-if="locale === 'de'">Links</label>
                            <label v-else>Links (Übersetzung {{ locale.toUpperCase() }})</label>
                            <div class="row" v-for="(link, index) in (locale === 'de' ? education.links : education.translations[locale].links)">
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
                        <div class="col-md-8">
                            <label v-if="locale === 'de'">Videos</label>
                            <label v-else>Videos (Übersetzung {{ locale.toUpperCase() }})</label>
                            <div class="row" v-for="(video, index) in (locale === 'de' ? education.videos : education.translations[locale].videos)">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" v-model="video.label" placeholder="Bezeichnung">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" v-model="video.value" placeholder="URL">
                                </div>
                                <div class="col-md-3">
                                    <button class="button error" @click="clickRemoveVideo(index)">Video entfernen</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <button class="button success" @click="clickAddVideo()">Video hinzufügen</button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="images">Bilder</label>
                            <image-selector id="images" :items="education.images" :locale="locale" @changed="updateImages"></image-selector>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="files">Dokumente</label>
                            <file-selector id="files" :items="education.files" :locale="locale" @changed="updateFiles"></file-selector>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="education-component-overlay" v-if="showPreview" @click="showPreview = false">

            <EmbedEducationsView @click.stop @clickClose="showPreview = false"
                               :education="education" :locale="locale"></EmbedEducationsView>

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
import ImageSelector from './ImageSelector';
import FileSelector from './FileSelector';
import EmbedEducationsView from "./EmbedEducationsView";
import Modal from './Modal';

export default {
    data() {
        return {
            locale: 'de',
            education: {
                isPublic: false,
                name: '',
                description: '',
                organizer: '',
                location: '',
                contact: '',
                text: '',
                educationTypes: [],
                languages: [],
                locations: [],
                links: [],
                videos: [],
                images: [],
                files: [],
                translations: {
                    fr: {
                        links: [],
                        videos: [],
                    },
                    it: {
                        links: [],
                        videos: [],
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
        EmbedEducationsView,
        TagSelector,
        ImageSelector,
        FileSelector,
        draggable,
        Modal,
    },
    computed: {
        ...mapState({
            selectedEducation: state => state.educations.education,
            educationTypes: state => state.educationTypes.all,
            languages: state => state.languages.all,
            locations: state => state.locations.all,
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
                            this.$store.dispatch('educations/delete', this.education.id).then(() => {
                                this.$router.push('/educations');
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
            this.$router.push('/educations');
        },
        clickSave() {

            if(this.education.id) {
                return this.$store.dispatch('educations/update', this.education).then(() => {
                    this.$router.push('/educations');
                });
            }

            this.$store.dispatch('educations/create', this.education).then(() => {
                this.$router.push('/educations');
            });

        },
        reload() {
            if(this.$route.params.id) {
                this.$store.commit('educations/set', {});
                this.$store.dispatch('educations/load', this.$route.params.id).then(() => {
                    this.education = {...this.selectedEducation};

                    if(!this.education.translations['fr'].videos) {
                        this.education.translations['fr'].videos = [];
                    }

                    if(!this.education.translations['it'].videos) {
                        this.education.translations['it'].videos = [];
                    }
                });
            }
        },
        clickRemoveProgram(programIndex) {
            if(this.education.programs[programIndex].units.length) {
                return this.modal = {
                    title: 'Programm entfernen',
                    description: 'Sind Sie sicher dass Sie diesen Eintrag unwiderruflich entfernen möchten?',
                    actions: [
                        {
                            label: 'Entfernen',
                            class: 'error',
                            onClick: () => {
                                this.education.programs.splice(programIndex, 1);
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

            this.education.programs.splice(programIndex, 1);
        },
        clickAddUnit(programIndex) {
            this.education.programs[programIndex].units.push({
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
            this.education.programs[programIndex].units[unitIndex].descriptions.push({
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
            let unit = this.education.programs[programIndex].units[unitIndex].descriptions.splice(descriptionIndex, 1)[0];
        },
        clickMoveUpUnit(programIndex, unitIndex) {
            let unit = this.education.programs[programIndex].units.splice(unitIndex, 1)[0];
            this.education.programs[programIndex].units.splice(unitIndex-1, 0, unit);
        },
        clickMoveDownUnit(programIndex, unitIndex) {
            let unit = this.education.programs[programIndex].units.splice(unitIndex, 1)[0];
            this.education.programs[programIndex].units.splice(unitIndex+1, 0, unit);
        },
        clickRemoveUnit(programIndex, unitIndex) {
            let unit = this.education.programs[programIndex].units.splice(unitIndex, 1)[0];
        },
        clickAddLink() {
            (this.locale === 'de' ? this.education.links : this.education.translations[this.locale].links).push({
                value: '',
                label: '',
            });
        },
        clickRemoveLink(index) {
            let link = (this.locale === 'de' ? this.education.links : this.education.translations[this.locale].links).splice(index, 1)[0];
        },
        clickAddVideo() {
            (this.locale === 'de' ? this.education.videos : this.education.translations[this.locale].videos).push({
                value: '',
                label: '',
            });
        },
        clickRemoveVideo(index) {
            let video = (this.locale === 'de' ? this.education.videos : this.education.translations[this.locale].videos).splice(index, 1)[0];
        },
        updateImages(images) {
            this.education.images = images;
        },
        updateFiles(files) {
            this.education.files = files;
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