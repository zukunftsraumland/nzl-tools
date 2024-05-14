<template>

    <div class="event-component">

        <div class="event-component-form">

            <div class="event-component-form-header">

                <h3>Eintrag erstellen</h3>

                <div class="event-component-form-header-actions">
                    <a class="button" @click="showPreview = true">Vorschau</a>
                    <a class="button" :href="'/api/v1/events/'+locale+'/'+event.id+'.ics'" target="_blank" v-if="event.id" :download="event.id+'.ics'">Kalendereintrag</a>
                    <a class="button warning" @click="event.isPublic = true" v-if="!event.isPublic">Entwurf</a>
                    <a class="button success" @click="event.isPublic = false" v-if="event.isPublic">Öffentlich</a>
                    <a @click="locale = 'de'" class="button" :class="{primary: locale === 'de'}">DE</a>
                    <a @click="locale = 'fr'" class="button" :class="{primary: locale === 'fr'}">FR</a>
                    <a @click="locale = 'it'" class="button" :class="{primary: locale === 'it'}">IT</a>
                    <a class="button" @click="clickDuplicate()" v-if="event.id">Duplizieren</a>
                    <a class="button error" @click="clickDelete()" v-if="event.id">Löschen</a>
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="event-component-form-section">

                <div class="row">
                    <div class="col-md-6" v-if="locale === 'de'">
                        <label for="title">Titel</label>
                        <input id="title" type="text" class="form-control" v-model="event.title" :placeholder="translate('title', event)">
                    </div>
                    <div class="col-md-6" v-else>
                        <label for="title">Titel (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="title" type="text" class="form-control" v-model="event.translations[locale].title" :placeholder="translate('title', event)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="description">Teaser</label>
                        <textarea name="description" id="description" class="form-control" rows="2" v-model="event.description" :placeholder="translate('description', event)"></textarea>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="description">Teaser (Übersetzung {{ locale.toUpperCase() }})</label>
                        <textarea name="description" id="description" class="form-control" rows="2" v-model="event.translations[locale].description" :placeholder="translate('description', event)"></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <a class="button success" :class="{warning: !event.isPromotedDE}" @click="event.isPromotedDE = !event.isPromotedDE">Hervorheben in DE</a>&nbsp;
                        <a class="button success" :class="{warning: !event.isPromotedFR}" @click="event.isPromotedFR = !event.isPromotedFR">Hervorheben in FR</a>&nbsp;
                        <a class="button success" :class="{warning: !event.isPromotedIT}" @click="event.isPromotedIT = !event.isPromotedIT">Hervorheben in IT</a>&nbsp;
                    </div>
                </div>

                <div class="event-component-form-section-group">

                    <div class="event-component-form-section-group-headline">Kategorisierung</div>

                    <div class="row">
                        <div class="col-md-3">
                            <label for="type">Typ</label>
                            <div class="select-wrapper">
                                <select class="form-control" v-model="event.type">
                                    <option value="external">Externe Veranstaltung</option>
                                    <option value="regiosuisse">regiosuisse Veranstaltung</option>
                                    <optgroup label="regiosuisse">
                                        <option value="fsk">FSK Sitzung</option>
                                        <option value="cafe-r">caféR</option>
                                        <option value="einstiegskurs">Einstiegskurs</option>
                                        <option value="konferenz">Konferenz</option>
                                        <option value="wissenschaftsforum">Wissenschaftsforum</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3" v-if="event.type !== 'external'">
                            <label for="color">Farbe</label>
                            <div class="select-wrapper">
                                <select class="form-control" v-model="event.color">
                                    <option :value="null"></option>
                                    <option v-for="color in colors" :value="color.code">{{ color.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <label for="topics">Thema</label>
                            <tag-selector id="topics" :model="event.topics"
                                          :options="topics.filter(topic => !topic.context || topic.context === 'event')" :searchType="'select'"></tag-selector>
                        </div>
                        <div class="col-md-3">
                            <label for="languages">Sprachen</label>
                            <tag-selector id="languages" :model="event.languages"
                                          :options="languages.filter(language => !language.context || language.context === 'event')" :searchType="'select'"></tag-selector>
                        </div>
                        <div class="col-md-3">
                            <label for="locations">Durchführungsort</label>
                            <tag-selector id="locations" :model="event.locations"
                                          :options="locations.filter(location => !location.context || location.context === 'event')" :searchType="'select'"></tag-selector>
                        </div>
                    </div>

                </div>

                <div class="event-component-form-section-group">

                    <div class="event-component-form-section-group-headline">Kontaktdetails</div>

                    <div class="row">
                        <div class="col-md-4" v-if="locale === 'de'">
                            <label for="location">Terminort</label>
                            <input id="location" type="text" class="form-control" v-model="event.location" :placeholder="translate('location', event)">
                        </div>
                        <div class="col-md-4" v-else>
                            <label for="location">Terminort (Übersetzung {{ locale.toUpperCase() }})</label>
                            <input id="location" type="text" class="form-control" v-model="event.translations[locale].location" :placeholder="translate('location', event)">
                        </div>
                        <div class="col-md-4" v-if="locale === 'de'">
                            <label for="organizer">Veranstalter</label>
                            <input id="organizer" type="text" class="form-control" v-model="event.organizer" :placeholder="translate('organizer', event)">
                        </div>
                        <div class="col-md-4" v-else>
                            <label for="organizer">Veranstalter (Übersetzung {{ locale.toUpperCase() }})</label>
                            <input id="organizer" type="text" class="form-control" v-model="event.translations[locale].organizer" :placeholder="translate('organizer', event)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4" v-if="locale === 'de'">
                            <label for="contact">Kontakt</label>
                            <textarea name="contact" id="contact" class="form-control" rows="3" v-model="event.contact" :placeholder="translate('contact', event)"></textarea>
                        </div>
                        <div class="col-md-4" v-else>
                            <label for="contact">Kontakt (Übersetzung {{ locale.toUpperCase() }})</label>
                            <textarea name="contact" id="contact" class="form-control" rows="3" v-model="event.translations[locale].contact" :placeholder="translate('contact', event)"></textarea>
                        </div>
                    </div>

                </div>

                <div class="event-component-form-section-group">

                    <div class="event-component-form-section-group-headline">Termindetails</div>

                    <div class="row">
                        <div class="col-md-2">
                            <label for="startDate">Startdatum</label>
                            <date-picker mode="dateTime" :is24hr="true" v-model="event.startDate" @update:modelValue="!event.endDate ? event.endDate = event.startDate : null" :locale="'de'">
                                <template v-slot="{ inputValue, inputEvents }">
                                    <input type="text" class="form-control"
                                           :value="inputValue"
                                           v-on="inputEvents"
                                           id="startDate">
                                </template>
                            </date-picker>
                        </div>
                        <div class="col-md-2">
                            <label for="endDate">Enddatum</label>
                            <date-picker mode="dateTime" :is24hr="true" v-model="event.endDate" :locale="'de'">
                                <template v-slot="{ inputValue, inputEvents }">
                                    <input type="text" class="form-control"
                                           :value="inputValue"
                                           v-on="inputEvents"
                                           id="endDate">
                                </template>
                            </date-picker>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8" v-if="locale === 'de'">
                            <label for="text">Beschreibung</label>
                            <ckeditor id="text" :editor="editor" :config="editorConfig"
                                      v-model="event.text" :placeholder="translate('text', event)"></ckeditor>
                        </div>
                        <div class="col-md-8" v-else>
                            <label for="text">Beschreibung (Übersetzung {{ locale.toUpperCase() }})</label>
                            <ckeditor id="text" :editor="editor" :config="editorConfig"
                                      v-model="event.translations[locale].text" :placeholder="translate('text', event)"></ckeditor>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-12">

                            <div class="event-component-form-section-program" v-for="(program, index) in event.programs">

                                <div class="row">
                                    <div class="col-md-6" v-if="locale === 'de'">
                                        <label :for="'title-'+index">Programmtitel</label>
                                        <input :id="'title-'+index" type="text" class="form-control" v-model="program.title" :placeholder="translate('title', program)">
                                    </div>
                                    <div class="col-md-6" v-else>
                                        <label :for="'title-'+index">Programmtitel (Übersetzung {{ locale.toUpperCase() }})</label>
                                        <input :id="'title-'+index" type="text" class="form-control" v-model="program.translations[locale].title" :placeholder="translate('title', program)">
                                    </div>
                                </div>

                                <div class="event-component-form-section-program-rows">

                                    <div class="event-component-form-section-program-rows-row" v-for="(unit, unitIndex) in program.units">

                                        <div class="row">
                                            <div class="col-md-2" v-if="locale === 'de'">
                                                <label :for="'time-'+index+'-'+unitIndex">Uhrzeit</label>
                                                <input :id="'time-'+index+'-'+unitIndex" type="text" class="form-control" v-model="unit.time" :placeholder="translate('time', unit)">
                                            </div>
                                            <div class="col-md-2" v-else>
                                                <label :for="'time-'+index+'-'+unitIndex">Uhrzeit (Übersetzung {{ locale.toUpperCase() }})</label>
                                                <input :id="'time-'+index+'-'+unitIndex" type="text" class="form-control" v-model="unit.translations[locale].time" :placeholder="translate('time', unit)">
                                            </div>
                                            <div class="col-md-8">
                                                <div class="event-component-form-section-program-rows-row-parts">
                                                    <div class="event-component-form-section-program-rows-row-parts-part" v-for="(description, descriptionIndex) in unit.descriptions">
                                                        <template v-if="locale === 'de'">
                                                            <label :for="'description-'+index+'-'+unitIndex+'-'+descriptionIndex">
                                                                Beschreibung
                                                                <span class="material-icons" v-if="descriptionIndex > 0"
                                                                   @click="clickRemoveUnitDescription(index, unitIndex, descriptionIndex)">cancel</span>
                                                            </label>
                                                            <ckeditor :id="'description-'+index+'-'+unitIndex+'-'+descriptionIndex" :editor="editor" :config="simpleEditorConfig"
                                                                      v-model="unit.descriptions[descriptionIndex].value" :placeholder="translate('value', description)"></ckeditor>
                                                        </template>
                                                        <template v-else>
                                                            <label :for="'description-'+index+'-'+unitIndex+'-'+descriptionIndex">
                                                                Beschreibung (Übersetzung {{ locale.toUpperCase() }})
                                                                <span class="material-icons" v-if="descriptionIndex > 0"
                                                                   @click="clickRemoveUnitDescription(index, unitIndex, descriptionIndex)">material-icons</span>
                                                            </label>
                                                            <ckeditor :id="'description-'+index+'-'+unitIndex+'-'+descriptionIndex" :editor="editor" :config="simpleEditorConfig"
                                                                      v-model="unit.descriptions[descriptionIndex].translations[locale].value" :placeholder="translate('value', description)"></ckeditor>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="event-component-form-section-program-rows-row-actions">
                                                    <button class="button" :class="{disabled: unitIndex === 0}" @click="clickMoveUpUnit(index, unitIndex)">
                                                        <span class="material-icons">keyboard_arrow_up</span>
                                                    </button>
                                                    <button class="button" :class="{disabled: unitIndex >= program.units.length-1}" @click="clickMoveDownUnit(index, unitIndex)">
                                                        <span class="material-icons">keyboard_arrow_down</span>
                                                    </button>
                                                    <button class="button" @click="clickAddUnitDescription(index, unitIndex)">
                                                        <span class="material-icons">view_column</span>
                                                    </button>
                                                    <button class="button error" @click="clickRemoveUnit(index, unitIndex)">
                                                        Einheit entfernen
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="event-component-form-section-program-rows-actions">

                                        <button class="button" @click="clickAddUnit(index)">Einheit hinzufügen</button>

                                        <button class="button error" @click="clickRemoveProgram(index)">Programm entfernen</button>

                                    </div>

                                </div>

                            </div>

                            <button class="button success" @click="clickAddProgram()">Programm hinzufügen</button>

                        </div>

                    </div>

                </div>

                <div class="event-component-form-section-group">

                    <div class="event-component-form-section-group-headline">Weiterführende Informationen</div>

                    <div class="row">
                        <div class="col-md-6" v-if="locale === 'de'">
                            <label for="registration">Link zur Anmeldung</label>
                            <input id="registration" type="text" class="form-control" v-model="event.registration" :placeholder="translate('registration', event)">
                        </div>
                        <div class="col-md-6" v-else>
                            <label for="registration">Link zur Anmeldung (Übersetzung {{ locale.toUpperCase() }})</label>
                            <input id="registration" type="text" class="form-control" v-model="event.translations[locale].registration" :placeholder="translate('registration', event)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <label v-if="locale === 'de'">Links</label>
                            <label v-else>Links (Übersetzung {{ locale.toUpperCase() }})</label>
                            <div class="row" v-for="(link, index) in (locale === 'de' ? event.links : event.translations[locale].links)">
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
                            <div class="row" v-for="(video, index) in (locale === 'de' ? event.videos : event.translations[locale].videos)">
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
                            <image-selector id="images" :items="event.images" :locale="locale" @changed="updateImages"></image-selector>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="files">Dokumente</label>
                            <file-selector id="files" :items="event.files" :locale="locale" @changed="updateFiles"></file-selector>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="event-component-overlay" v-if="showPreview" @click="showPreview = false">

            <EmbedEventsView @click.stop @clickClose="showPreview = false"
                             :event="event" :locale="locale"></EmbedEventsView>

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
    import { DatePicker } from 'v-calendar';
    import Modal from './Modal';
    import EmbedEventsView from './EmbedEventsView';

    export default {
        data() {
            return {
                locale: 'de',
                event: {
                    isPublic: false,
                    isPromotedDE: false,
                    isPromotedFR: false,
                    isPromotedIT: false,
                    title: '',
                    description: '',
                    organizer: '',
                    location: '',
                    contact: '',
                    text: '',
                    type: 'external',
                    color: null,
                    startDate: null,
                    endDate: null,
                    topics: [],
                    languages: [],
                    locations: [],
                    programs: [],
                    registration: '',
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
                colors: [
                    {
                        id: 1,
                        name: 'Grün (Allgemein)',
                        code: '#B4BE00',
                    },
                    {
                        id: 2,
                        name: 'Blau (WiGe und Plattformen)',
                        code: '#0093D1',
                    },
                    {
                        id: 3,
                        name: 'Orange (formation-regiosuisse)',
                        code: '#FF7D00',
                    },
                    {
                        id: 4,
                        name: 'Rot (Forschung)',
                        code: '#DC0019',
                    },
                ],
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
            EmbedEventsView,
            TagSelector,
            ImageSelector,
            FileSelector,
            DatePicker,
            draggable,
            Modal,
        },
        computed: {
            ...mapState({
                selectedEvent: state => state.events.event,
                topics: state => state.topics.all,
                languages: state => state.languages.all,
                locations: state => state.locations.all,
            }),
        },
        methods: {
            clickDuplicate() {
                this.$router.replace('/events/add?copy='+this.event.id);
            },
            clickDelete () {
                this.modal = {
                    title: 'Eintrag löschen',
                    description: 'Sind Sie sicher dass Sie diesen Eintrag unwiderruflich löschen möchten?',
                    actions: [
                        {
                            label: 'Endgültig löschen',
                            class: 'error',
                            onClick: () => {
                                this.$store.dispatch('events/delete', this.event.id).then(() => {
                                    this.$router.push('/events');
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
                this.$router.push('/events');
            },
            clickSave() {

                if(!this.event.startDate) {
                    this.event.startDate = null;
                }

                if(!this.event.endDate) {
                    this.event.endDate = null;
                }

                if(!this.event.color) {
                    this.event.color = null;
                }

                if(this.event.id) {
                    return this.$store.dispatch('events/update', this.event).then(() => {
                        this.$router.push('/events');
                    });
                }

                this.$store.dispatch('events/create', this.event).then(() => {
                    this.$router.push('/events');
                });
            },
            reload() {
                if(this.$route.params.id) {
                    this.$store.commit('events/set', {});
                    this.$store.dispatch('events/load', this.$route.params.id).then(() => {
                        this.event = {...this.selectedEvent};

                        if(!this.event.translations['fr'].videos) {
                            this.event.translations['fr'].videos = [];
                        }

                        if(!this.event.translations['it'].videos) {
                            this.event.translations['it'].videos = [];
                        }
                    });

                    return;
                }

                if(this.$route.query?.copy) {
                    this.$store.dispatch('events/load', this.$route.query?.copy).then((event) => {
                        if(event) {
                            this.event = JSON.parse(JSON.stringify({...event}));
                            delete this.event.id;
                        }
                    })
                }
            },
            clickAddProgram() {
                this.event.programs.push({
                    title: '',
                    units: [
                        {
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
                        },
                    ],
                    translations: {
                        'fr': {
                            title: '',
                        },
                        'it': {
                            title: '',
                        },
                    },
                });
            },
            clickRemoveProgram(programIndex) {
                if(this.event.programs[programIndex].units.length) {
                    return this.modal = {
                        title: 'Programm entfernen',
                        description: 'Sind Sie sicher dass Sie diesen Eintrag unwiderruflich entfernen möchten?',
                        actions: [
                            {
                                label: 'Entfernen',
                                class: 'error',
                                onClick: () => {
                                    this.event.programs.splice(programIndex, 1);
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

                this.event.programs.splice(programIndex, 1);
            },
            clickAddUnit(programIndex) {
                this.event.programs[programIndex].units.push({
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
                this.event.programs[programIndex].units[unitIndex].descriptions.push({
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
                let unit = this.event.programs[programIndex].units[unitIndex].descriptions.splice(descriptionIndex, 1)[0];
            },
            clickMoveUpUnit(programIndex, unitIndex) {
                let unit = this.event.programs[programIndex].units.splice(unitIndex, 1)[0];
                this.event.programs[programIndex].units.splice(unitIndex-1, 0, unit);
            },
            clickMoveDownUnit(programIndex, unitIndex) {
                let unit = this.event.programs[programIndex].units.splice(unitIndex, 1)[0];
                this.event.programs[programIndex].units.splice(unitIndex+1, 0, unit);
            },
            clickRemoveUnit(programIndex, unitIndex) {
                let unit = this.event.programs[programIndex].units.splice(unitIndex, 1)[0];
            },
            clickAddLink() {
                (this.locale === 'de' ? this.event.links : this.event.translations[this.locale].links).push({
                    value: '',
                    label: '',
                });
            },
            clickRemoveLink(index) {
                let link = (this.locale === 'de' ? this.event.links : this.event.translations[this.locale].links).splice(index, 1)[0];
            },
            clickAddVideo() {
                (this.locale === 'de' ? this.event.videos : this.event.translations[this.locale].videos).push({
                    value: '',
                    label: '',
                });
            },
            clickRemoveVideo(index) {
                let video = (this.locale === 'de' ? this.event.videos : this.event.translations[this.locale].videos).splice(index, 1)[0];
            },
            updateImages(images) {
                this.event.images = images;
            },
            updateFiles(files) {
                this.event.files = files;
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