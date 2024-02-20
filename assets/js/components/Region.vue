<template>

    <div class="region-component">

        <div class="region-component-form">

            <div class="region-component-form-header">

                <h3>Eintrag erstellen</h3>

                <div class="region-component-form-header-actions">
                    <!--<a class="button" @click="showPreview = true">Vorschau</a>-->
                    <a class="button warning" @click="region.isPublic = true" v-if="!region.isPublic">Entwurf</a>
                    <a class="button success" @click="region.isPublic = false" v-if="region.isPublic">Öffentlich</a>
                    <a @click="locale = 'de'" class="button" :class="{primary: locale === 'de'}">DE</a>
                    <a @click="locale = 'fr'" class="button" :class="{primary: locale === 'fr'}">FR</a>
                    <a @click="locale = 'it'" class="button" :class="{primary: locale === 'it'}">IT</a>
                    <a class="button error" @click="clickDelete()" v-if="region.id">Löschen</a>
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="region-component-form-section">

                <div class="row">
                    <div class="col-md-6" v-if="locale === 'de'">
                        <label for="name">Bezeichnung</label>
                        <input id="name" type="text" class="form-control" v-model="region.name" :placeholder="translateField(region,'name', locale)">
                    </div>
                    <div class="col-md-6" v-else>
                        <label for="name">Bezeichnung (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="name" type="text" class="form-control" v-model="region.translations[locale].name" :placeholder="translateField(region,'name', locale)">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6" v-if="locale === 'de'">
                        <label for="url">URL</label>
                        <input id="url" type="text" class="form-control" v-model="region.url" :placeholder="translateField(region,'url', locale)">
                    </div>
                    <div class="col-md-6" v-else>
                        <label for="url">URL (Übersetzung {{ locale.toUpperCase() }})</label>
                        <input id="url" type="text" class="form-control" v-model="region.translations[locale].url" :placeholder="translateField(region,'url', locale)">
                    </div>
                    <div class="col-md-2">
                        <label for="color">Farbe</label>
                        <input id="color" type="text" class="form-control" v-model="region.color">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8" v-if="locale === 'de'">
                        <label for="description">Beschreibung</label>
                        <ckeditor id="description" :editor="editor" :config="simpleEditorConfig"
                                  v-model="region.description" :placeholder="translateField('description', region)"></ckeditor>
                    </div>
                    <div class="col-md-8" v-else>
                        <label for="description">Beschreibung (Übersetzung {{ locale.toUpperCase() }})</label>
                        <ckeditor id="description" :editor="editor" :config="simpleEditorConfig"
                                  v-model="region.translations[locale].description" :placeholder="translateField('description', region)"></ckeditor>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-9">
                        <label for="contacts">Kontakte</label>
                        <tag-selector id="contacts" :model="region.contacts"
                                      :options="contacts" :searchType="'select'"></tag-selector>
                    </div>
                </div>

                <div class="region-component-form-section-group">

                    <div class="region-component-form-section-group-headline">Kategorisierung</div>

                    <div class="row">
                        <div class="col-md-3">
                            <label for="type">Typ</label>
                            <div class="select-wrapper">
                                <select class="form-control" v-model="region.type">
                                    <option :value="null"></option>
                                    <option v-for="regionType in regionTypes" :value="regionType.id">{{ regionType.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label for="cities">Gemeinde</label>
                            <tag-selector id="cities" :model="region.cities"
                                          :options="cities.filter(city => !city.context || city.context === 'region')" :searchType="'select'"></tag-selector>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!--<div class="region-component-overlay" v-if="showPreview" @click="showPreview = false">

            <EmbedRegionsView @click.stop @clickClose="showPreview = false"
                               :region="region" :locale="locale"></EmbedRegionsView>

        </div>-->


        <transition name="fade">
            <Modal v-if="modal" :config="modal"></Modal>
        </transition>

    </div>

</template>

<script>
import { mapState } from 'vuex';
import TagSelector from './TagSelector';
//import EmbedRegionsView from "./EmbedRegionsView";
import Modal from './Modal';
import {translateField} from '../utils/filters';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default {
    data() {
        return {
            locale: 'de',
            region: {
                isPublic: false,
                name: '',
                type: null,
                url: null,
                color: null,
                description: null,
                cities: [],
                contacts: [],
                translations: {
                    fr: {},
                    it: {},
                },
            },
            regionTypes: [
                {
                    id: 'nrp',
                    name: 'NRP-Regionen'
                },
                {
                    id: 'intercantonal',
                    name: 'Überkantonale Programme'
                },
                {
                    id: 'ris',
                    name: 'Regionale Innovationssysteme (RIS)'
                },
                {
                    id: 'cantonal',
                    name: 'Kantonale NRP-Fachstellen'
                },
                {
                    id: 'energy',
                    name: 'Energieregionen'
                },
            ],
            showPreview: false,
            modal: null,
            editor: ClassicEditor,
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
        //EmbedRegionsView,
        TagSelector,
        Modal,
    },
    computed: {
        ...mapState({
            selectedRegion: state => state.regions.region,
            cities: state => state.cities.all,
            contacts: state => state.contacts.all,
        }),
    },
    methods: {
        translateField,
        clickDelete () {
            this.modal = {
                title: 'Eintrag löschen',
                description: 'Sind Sie sicher dass Sie diesen Eintrag unwiderruflich löschen möchten?',
                actions: [
                    {
                        label: 'Endgültig löschen',
                        class: 'error',
                        onClick: () => {
                            this.$store.dispatch('regions/delete', this.region.id).then(() => {
                                this.$router.push('/regions');
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
            this.$router.push('/regions');
        },
        clickSave() {

            if(this.region.id) {
                return this.$store.dispatch('regions/update', this.region).then(() => {
                    this.$router.push('/regions');
                });
            }

            this.$store.dispatch('regions/create', this.region).then(() => {
                this.$router.push('/regions');
            });

        },
        reload() {
            if(this.$route.params.id) {
                this.$store.commit('regions/set', {});
                this.$store.dispatch('regions/load', this.$route.params.id).then(() => {
                    this.region = {...this.selectedRegion};
                });
            }
        },
    },
    created () {
        this.reload();
    }
}
</script>