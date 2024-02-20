<template>

    <div class="financial-support-component">

        <div class="financial-support-component-form">

            <div class="financial-support-component-form-header">

                <h3>Eintrag erstellen</h3>

                <div class="financial-support-component-form-header-actions">
                    <a class="button warning" @click="financialSupport.isPublic = true" v-if="!financialSupport.isPublic">Entwurf</a>
                    <a class="button success" @click="financialSupport.isPublic = false" v-if="financialSupport.isPublic">Öffentlich</a>
                    <a @click="locale = 'de'" class="button" :class="{primary: locale === 'de'}">DE</a>
                    <a @click="locale = 'fr'" class="button" :class="{primary: locale === 'fr'}">FR</a>
                    <a @click="locale = 'it'" class="button" :class="{primary: locale === 'it'}">IT</a>
                    <a class="button error" @click="clickDelete()" v-if="financialSupport.id">Löschen</a>
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="financial-support-component-form-section">

                <div class="row">
                    <div class="col-md-6" v-if="locale === 'de'">
                        <label for="title">Bezeichnung</label>
                        <input id="title" type="text" class="form-control" v-model="financialSupport.name" :placeholder="translateField(financialSupport, 'name', locale)">
                    </div>
                    <div class="col-md-6" v-else>
                        <label for="title">Bezeichnung (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="title" type="text" class="form-control" v-model="financialSupport.translations[locale].name" :placeholder="translateField(financialSupport, 'name', locale)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12" v-if="locale === 'de'">
                        <label for="images">Logo</label>
                        <image-selector id="images" :multiple="false" :item="financialSupport.logo" :locale="locale" @changed="financialSupport.logo = $event"></image-selector>
                    </div>
                    <div class="col-md-12" v-else>
                        <label for="images">Logo (Übersetzung {{ locale.toUpperCase() }})</label>
                        <image-selector id="images" :multiple="false" :item="financialSupport.translations[locale].logo" :locale="'de'" @changed="financialSupport.translations[locale].logo = $event"></image-selector>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label for="startDate">Laufzeit (Start)</label>
                        <date-picker mode="date" :is24hr="true" v-model="financialSupport.startDate" @update:modelValue="!financialSupport.endDate ? financialSupport.endDate = financialSupport.startDate : null" :locale="'de'">
                            <template v-slot="{ inputValue, inputEvents }">
                                <input type="text" class="form-control"
                                       :value="inputValue"
                                       v-on="inputEvents"
                                       id="startDate">
                            </template>
                        </date-picker>
                    </div>
                    <div class="col-md-3">
                        <label for="endDate">Laufzeit (Ende)</label>
                        <date-picker mode="date" :is24hr="true" v-model="financialSupport.endDate" :locale="'de'">
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
                    <div class="col-md-6">
                        <label for="authorities">Förderstelle</label>
                        <tag-selector id="authorities" :model="financialSupport.authorities"
                                      :options="authorities.filter(authority => !authority.context || authority.context === 'financial-support')" :searchType="'select'"></tag-selector>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="states">Kantone</label>
                        <tag-selector id="states" :model="financialSupport.states"
                                      :options="states.filter(state => !state.context || state.context === 'financial-support')" :searchType="'select'"></tag-selector>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="text">Zusammenfassung</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="financialSupport.description" :placeholder="translateField(financialSupport, 'description', locale)"></ckeditor>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="text">Zusammenfassung (Übersetzung {{ locale.toUpperCase() }})</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="financialSupport.translations[locale].description" :placeholder="translateField(financialSupport, 'description', locale)"></ckeditor>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="beneficiaries">Begünstigte</label>
                        <tag-selector id="beneficiaries" :model="financialSupport.beneficiaries"
                                      :options="beneficiaries.filter(beneficiary => !beneficiary.context || beneficiary.context === 'financial-support')" :searchType="'select'"></tag-selector>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="text">Kurzbeschrieb</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="financialSupport.additionalInformation" :placeholder="translateField(financialSupport, 'additionalInformation', locale)"></ckeditor>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="text">Kurzbeschrieb (Übersetzung {{ locale.toUpperCase() }})</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="financialSupport.translations[locale].additionalInformation" :placeholder="translateField(financialSupport, 'additionalInformation', locale)"></ckeditor>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="text">Teilnahmebedingungen</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="financialSupport.inclusionCriteria" :placeholder="translateField(financialSupport, 'inclusionCriteria', locale)"></ckeditor>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="text">Teilnahmebedingungen (Übersetzung {{ locale.toUpperCase() }})</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="financialSupport.translations[locale].inclusionCriteria" :placeholder="translateField(financialSupport, 'inclusionCriteria', locale)"></ckeditor>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="text">Ausschlusskriterien</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="financialSupport.exclusionCriteria" :placeholder="translateField(financialSupport, 'exclusionCriteria', locale)"></ckeditor>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="text">Ausschlusskriterien (Übersetzung {{ locale.toUpperCase() }})</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="financialSupport.translations[locale].exclusionCriteria" :placeholder="translateField(financialSupport, 'exclusionCriteria', locale)"></ckeditor>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="text">Gesuchstellung</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="financialSupport.application" :placeholder="translateField(financialSupport, 'application', locale)"></ckeditor>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="text">Gesuchstellung (Übersetzung {{ locale.toUpperCase() }})</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="financialSupport.translations[locale].application" :placeholder="translateField(financialSupport, 'application', locale)"></ckeditor>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="topics">Thema</label>
                        <tag-selector id="topics" :model="financialSupport.topics"
                                      :options="topics.filter(topic => !topic.context || topic.context === 'financial-support')" :searchType="'select'"></tag-selector>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="projectTypes">Projekttyp</label>
                        <tag-selector id="projectTypes" :model="financialSupport.projectTypes"
                                      :options="projectTypes.filter(projectType => !projectType.context || projectType.context === 'financial-support')" :searchType="'select'"></tag-selector>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="instruments">Unterstützungsarten</label>
                        <tag-selector id="instruments" :model="financialSupport.instruments"
                                      :options="instruments.filter(instrument => !instrument.context || instrument.context === 'financial-support')" :searchType="'select'"></tag-selector>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="text">Finanzierung</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="financialSupport.financingRatio" :placeholder="translateField(financialSupport, 'financingRatio', locale)"></ckeditor>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="text">Finanzierung (Übersetzung {{ locale.toUpperCase() }})</label>
                        <ckeditor id="text" :editor="editor" :config="editorConfig"
                                  v-model="financialSupport.translations[locale].financingRatio" :placeholder="translateField(financialSupport, 'financingRatio', locale)"></ckeditor>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="geographicRegions">Geographische Region</label>
                        <tag-selector id="geographicRegions" :model="financialSupport.geographicRegions"
                                      :options="geographicRegions.filter(geographicRegion => !geographicRegion.context || geographicRegion.context === 'financial-support')" :searchType="'select'"></tag-selector>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="contact">Relevanz für Regionale Entwicklungsstrategien (RES)</label>
                        <textarea name="res" id="res" class="form-control" rows="3" v-model="financialSupport.res" :placeholder="translateField(financialSupport, 'res', locale)"></textarea>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="contact">Relevanz für Regionale Entwicklungsstrategien (RES) (Übersetzung {{ locale.toUpperCase() }})</label>
                        <textarea name="res" id="res" class="form-control" rows="3" v-model="financialSupport.translations[locale].res" :placeholder="translateField(financialSupport, 'res', locale)"></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <label v-if="locale === 'de'">Links</label>
                        <label v-else>Links (Übersetzung {{ locale.toUpperCase() }})</label>
                        <div class="row" v-for="(link, index) in (locale === 'de' ? financialSupport.links : financialSupport.translations[locale].links)">
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
                        <label v-if="locale === 'de'">Kontakte</label>
                        <label v-else>Kontakte (Übersetzung {{ locale.toUpperCase() }})</label>
                        <div class="financial-support-component-form-section-contact" v-for="(contact, index) in (locale === 'de' ? financialSupport.contacts : financialSupport.translations[locale].contacts)">
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Name</label>
                                    <input type="text" class="form-control" v-model="contact.name">
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
                                    <input type="text" class="form-control" v-model="contact.title">
                                </div>
                                <div class="col-md-4">
                                    <label>Vorname</label>
                                    <input type="text" class="form-control" v-model="contact.firstName">
                                </div>
                                <div class="col-md-4">
                                    <label>Nachname</label>
                                    <input type="text" class="form-control" v-model="contact.lastName">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Funktion</label>
                                    <input type="text" class="form-control" v-model="contact.role">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Telefon</label>
                                    <input type="text" class="form-control" v-model="contact.phone">
                                </div>
                                <div class="col-md-4">
                                    <label>E-Mail</label>
                                    <input type="text" class="form-control" v-model="contact.email">
                                </div>
                                <div class="col-md-4">
                                    <label>Website</label>
                                    <input type="text" class="form-control" v-model="contact.website">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <label>Strasse</label>
                                    <input type="text" class="form-control" v-model="contact.street">
                                </div>
                                <div class="col-md-3">
                                    <label>PLZ</label>
                                    <input type="text" class="form-control" v-model="contact.zipCode">
                                </div>
                                <div class="col-md-4">
                                    <label>Ort</label>
                                    <input type="text" class="form-control" v-model="contact.city">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="button warning" @click="clickRemoveContact(index)">Kontakt entfernen</div>
                                </div>
                            </div>
                        </div>
                        <div class="financial-support-component-form-section-contact">
                            <div class="button success" @click="clickAddContact()">Kontakt hinzufügen</div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <transition name="fade">
            <Modal v-if="modal" :config="modal"></Modal>
        </transition>

    </div>

</template>

<script>
import {mapGetters, mapState} from 'vuex';
    import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import TagSelector from './TagSelector';
    import ImageSelector from './ImageSelector';
    import FileSelector from './FileSelector';
    import { DatePicker } from 'v-calendar';
    import Modal from './Modal';
    import {translateField} from '../utils/filters';

    export default {
        data() {
            return {
                locale: 'de',
                financialSupport: {
                    position: 10000,
                    isPublic: false,
                    name: '',
                    description: '',
                    additionalInformation: '',
                    policies: '',
                    application: '',
                    inclusionCriteria: '',
                    exclusionCriteria: '',
                    financingRatio: '',
                    res: '',
                    startDate: null,
                    endDate: null,
                    links: [],
                    logo: null,
                    authorities: [],
                    states: [],
                    beneficiaries: [],
                    topics: [],
                    projectTypes: [],
                    instruments: [],
                    geographicRegions: [],
                    contacts: [],
                    translations: {
                        fr: {
                            links: [],
                            contacts: [],
                        },
                        it: {
                            links: [],
                            contacts: [],
                        },
                    },
                },
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
            };
        },
        components: {
            TagSelector,
            ImageSelector,
            FileSelector,
            DatePicker,
            Modal,
        },
        computed: {
            ...mapState({
                selectedFinancialSupport: state => state.financialSupports.financialSupport,
                authorities: state => state.authorities.all,
                states: state => state.states.all,
                beneficiaries: state => state.beneficiaries.all,
                topics: state => state.topics.all,
                projectTypes: state => state.projectTypes.all,
                instruments: state => state.instruments.all,
                geographicRegions: state => state.geographicRegions.all,
            }),
            ...mapGetters({
                getAuthorityById: 'authorities/getById',
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
                                this.$store.dispatch('financialSupports/delete', this.financialSupport.id).then(() => {
                                    this.$router.push('/financial-supports');
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
                this.$router.push('/financial-supports');
            },
            clickSave() {

                if(!this.financialSupport.startDate) {
                    this.financialSupport.startDate = null;
                }

                if(!this.financialSupport.endDate) {
                    this.financialSupport.endDate = null;
                }

                if(this.financialSupport.id) {
                    return this.$store.dispatch('financialSupports/update', this.financialSupport).then(() => {
                        this.$router.push('/financial-supports');
                    });
                }

                this.$store.dispatch('financialSupports/create', this.financialSupport).then(() => {
                    this.$router.push('/financial-supports');
                });
            },
            reload() {
                if(this.$route.params.id) {
                    this.$store.commit('financialSupports/set', {});
                    this.$store.dispatch('financialSupports/load', this.$route.params.id).then(() => {
                        this.financialSupport = {...this.selectedFinancialSupport};
                    });
                }
            },
            clickAddLink() {
                (this.locale === 'de' ? this.financialSupport.links : this.financialSupport.translations[this.locale].links).push({
                    value: '',
                    label: '',
                });
            },
            clickRemoveLink(index) {
                let link = (this.locale === 'de' ? this.financialSupport.links : this.financialSupport.translations[this.locale].links).splice(index, 1)[0];
            },
            clickAddContact() {
                (this.locale === 'de' ? this.financialSupport.contacts : this.financialSupport.translations[this.locale].contacts).push({});
            },
            clickRemoveContact(index) {
                let contact = (this.locale === 'de' ? this.financialSupport.contacts : this.financialSupport.translations[this.locale].contacts).splice(index, 1)[0];
            },
            translateField,
        },
        created () {
            this.reload();
        }
    }
</script>