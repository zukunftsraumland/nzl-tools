<template>

    <div class="embed-regions" :class="[$env.INSTANCE_ID+'-regions', {'is-responsive': responsive}]" @click.stop="clickInside">

        <div class="embed-regions-search">

            <div class="embed-regions-search-input">
                <input type="text" :placeholder="viewMode === 'city' ? selectedCity.name : $t('Nach Gemeinde suchen', locale)" v-model="term"
                       :class="{'has-value': (term || viewMode === 'city')}">
                <div class="embed-regions-search-input-icon" @click.stop="viewMode = 'map'" v-if="!term && viewMode === 'city'"></div>
                <div class="embed-regions-search-input-icon" @click.stop="term = null" v-else></div>
                <div class="embed-regions-search-input-result" v-if="term">
                    <div class="embed-regions-search-input-result-item"
                         v-for="city in searchResultItems" @click="clickSearchResultItem(city)">
                        {{ city.name }}
                    </div>
                    <div v-if="!searchResultItems.length">
                        <em>{{ $t('Keine Ergebnisse', locale) }}</em>
                    </div>
                </div>
            </div>

            <div class="embed-regions-search-input" v-if="viewMode === 'city'">
                <a @click="viewMode = 'map'">{{ $t('Zurück zur Kartenansicht', locale) }}</a>
            </div>

        </div>

        <template v-if="viewMode === 'city'">

            <div class="embed-regions-content" v-for="region in cityRegions">

                <div class="embed-regions-content-context">

                    <div class="embed-regions-content-context-data">

                        <div class="embed-regions-content-context-data-column">

                            <h2 class="embed-regions-content-context-data-column-title">{{ translateField(region, 'name', locale) }}</h2>

                            <div v-if="region.description" v-html="translateField(region, 'description', locale)"></div>

                            <p v-if="translateField(region, 'url', locale)">
                                <a :href="translateField(region, 'url', locale)" target="_blank">{{ $t('Mehr Informationen zur Region', locale) }}</a>
                            </p>

                            <p>
                                <strong>{{ $t('Gemeinden in dieser Region', locale) }}:</strong><br>
                                <template v-for="(city, idx) in region.cities.slice(0, showRegionCities[region.id] ? 1000 : 10)">
                                    {{ idx === 0 ? '' : ', ' }}
                                    {{ getCityById(city.id)?.name }}
                                </template>
                                <template v-if="region.cities.length > 10">
                                    /
                                    <a v-if="!showRegionCities[region.id]" @click="showRegionCities[region.id] = true">{{ $t('Weitere {count} Gemeinden anzeigen', locale, {count: region.cities.length - 10}) }}</a>
                                    <a v-else-if="showRegionCities[region.id]" @click="showRegionCities[region.id] = false">{{ $t('Weniger anzeigen', locale) }}</a>
                                </template>
                            </p>

                        </div>

                        <div class="embed-regions-content-context-data-column">

                            <div class="embed-regions-content-context-data-column-contact" v-if="!region.contacts.length">
                                <div class="embed-regions-content-context-data-column-contact-content">
                                    <p class="embed-regions-content-context-data-column-contact-content-description">
                                        <strong>{{ $t('Leider wurden keine Kontakte gefunden.', locale) }}</strong><br>
                                    </p>
                                    <p class="embed-regions-content-context-data-column-contact-content-description">
                                        <span v-html="$t('Versuchen Sie es in der <a href=\'https://regiosuisse.ch/adressen\'>Expertendatenbank</a>.', locale)"></span>
                                    </p>
                                </div>
                            </div>

                            <div class="embed-regions-content-context-data-column-contact" v-for="contact in region.contacts.map(contact => getContactById(contact.id)).filter(contact => contact)">

                                <div class="embed-regions-content-context-data-column-contact-content">

                                    <ContactAddress v-if="contact.type === 'company'"
                                                    :locale="locale"
                                                    :company="contact"></ContactAddress>

                                    <ContactAddress v-if="contact.type === 'person'"
                                                    :locale="locale"
                                                    :person="contact"
                                                    :employment="employments.find(e => e.id === contact.employments[0]?.id)"
                                                    :company="contacts.find(c => c.id === employments.find(e => e.id === contact.employments[0]?.id)?.company?.id)"></ContactAddress>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </template>

        <template v-else>

            <div class="embed-regions-content">

                <div class="embed-regions-content-map">

                    <RegionsMap
                            :locale="locale"
                            :region-type="regionType"
                            :active-city="selectedCity"
                            :active-region="selectedRegions[0] || null"
                            @clickCity="selectedCity = $event"
                            @clickRegion="clickMapRegion($event)"></RegionsMap>

                </div>

                <div class="embed-regions-content-context">

                    <div class="embed-regions-content-context-nav" v-if="regionTypes.length > 1">

                        <a class="embed-regions-content-context-nav-item"
                           v-for="rt in regionTypes"
                           @click="clickRegionType(rt.id)"
                           :class="{'active': regionType === rt.id}">{{ $t(rt.name, locale) }}</a>

                    </div>

                    <div class="embed-regions-content-context-data" v-for="selectedRegion in selectedRegions">

                        <div class="embed-regions-content-context-data-column">

                            <h2 class="embed-regions-content-context-data-column-title">{{ translateField(selectedRegion, 'name', locale) }}</h2>

                            <div v-if="selectedRegion.description" v-html="translateField(selectedRegion, 'description', locale)"></div>

                            <p v-if="translateField(selectedRegion, 'url', locale)">
                                <a :href="translateField(selectedRegion, 'url', locale)" target="_blank">{{ $t('Mehr Informationen zur Region', locale) }}</a>
                            </p>

                            <p>
                                <strong>{{ $t('Gemeinden in dieser Region', locale) }}:</strong><br>
                                <template v-for="(city, idx) in selectedRegion.cities.slice(0, showRegionCities[selectedRegion.id] ? 1000 : 10)">
                                    {{ idx === 0 ? '' : ', ' }}
                                    {{ getCityById(city.id)?.name }}
                                </template>
                                <template v-if="selectedRegion.cities.length > 10">
                                    /
                                    <a v-if="!showRegionCities[selectedRegion.id]" @click="showRegionCities[selectedRegion.id] = true">{{ $t('Weitere {count} Gemeinden anzeigen', locale, {count: selectedRegion.cities.length - 10}) }}</a>
                                    <a v-else-if="showRegionCities[selectedRegion.id]" @click="showRegionCities[selectedRegion.id] = false">{{ $t('Weniger anzeigen', locale) }}</a>
                                </template>
                            </p>


                        </div>

                        <div class="embed-regions-content-context-data-column">

                            <div class="embed-regions-content-context-data-column-contact" v-if="!selectedRegion.contacts.length">
                                <div class="embed-regions-content-context-data-column-contact-content">
                                    <p class="embed-regions-content-context-data-column-contact-content-description">
                                        <strong>{{ $t('Leider wurden keine Kontakte gefunden.', locale) }}</strong><br>
                                    </p>
                                    <p class="embed-regions-content-context-data-column-contact-content-description">
                                        <span v-html="$t('Versuchen Sie es in der <a href=\'https://regiosuisse.ch/adressen\'>Expertendatenbank</a>.', locale)"></span>
                                    </p>
                                </div>
                            </div>

                            <div class="embed-regions-content-context-data-column-contact" v-for="contact in selectedRegion.contacts.map(contact => getContactById(contact.id)).filter(contact => contact)">

                                <div class="embed-regions-content-context-data-column-contact-content">

                                    <ContactAddress v-if="contact.type === 'company'"
                                                    :locale="locale"
                                                    :company="contact"></ContactAddress>

                                    <ContactAddress v-if="contact.type === 'person'"
                                                    :locale="locale"
                                                    :person="contact"
                                                    :employment="employments.find(e => e.id === contact.employments[0]?.id)"
                                                    :company="contacts.find(c => c.id === employments.find(e => e.id === contact.employments[0]?.id)?.company?.id)"></ContactAddress>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </template>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import {track, trackDevice, trackPageView} from '../utils/logger';
import RegionsMap from './RegionsMap';
import ContactAddress from './ContactAddress.vue';

export default {

    components: {
        RegionsMap,
        ContactAddress,
    },

    data() {
        return {
            isLoading: false,
            term: '',
            region: null,
            regionType: 'nrp',
            viewMode: 'map',
            selectedCity: null,
            showRegionCities: {},
        };
    },

    computed: {
        locale () {
            return this.$clientOptions?.locale || 'de';
        },
        responsive () {
            return this.$clientOptions?.responsive ?? true;
        },
        fixedFilters () {
            return this.$clientOptions?.fixedFilters || [];
        },
        disableTelemetry () {
            return this.$clientOptions?.disableTelemetry || false;
        },
        history () {
            return this.$clientOptions?.history || false;
        },
        historyMode () {
            return this.$clientOptions?.history?.mode || 'query';
        },
        historyBase () {
            return this.$clientOptions?.history?.base || '';
        },
        regionTypes () {
            return this.$clientOptions?.regionTypes?.length ? this.$clientOptions?.regionTypes : [
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
                    name: 'Energie-Regionen'
                },
            ];
        },
        ...mapState({
            regions: function (state) {
                return state.regions.all
                    .filter(e => this.regionTypes.find(r => r.id === e.type))
                    .map(this.$clientOptions?.middleware?.mapRegions || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterRegions || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortRegions || ((a, b) => translateField(a, 'name', this.locale).localeCompare(translateField(b, 'name', this.locale))));
            },
            cities: function (state) {
                return state.cities.all
                    .filter(e => !e.context || e.context === 'region')
                    .map(this.$clientOptions?.middleware?.mapCities || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterCities || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortCities || ((a, b) => translateField(a, 'name', this.locale).localeCompare(translateField(b, 'name', this.locale))));
            },
            contacts: state => state.contacts.all,
            contactGroups: state => state.contactGroups.all,
            employments: state => state.employments.all,
        }),
        ...mapGetters({
            getCityById: 'cities/getById',
            getContactById: 'contacts/getById',
            getContactGroupById: 'contactGroups/getById',
            getStateById: 'states/getById',
        }),
        searchResultItems() {
            if(!this.term) {
                return [];
            }

            return this.cities.filter(city => city.name.toLowerCase().includes(this.term.toLowerCase()));
        },
        cityRegions() {
            if(!this.selectedCity) {
                return [];
            }

            return this.regions
                .filter(region => region.cities.find(city => city.id === this.selectedCity.id));
        },
        selectedRegions() {
            if(!this.selectedCity) {
                return [];
            }

            return this.regions
                .filter(region => region.type === this.regionType)
                .filter(region => region.cities.find(city => city.id === this.selectedCity.id));
        },
    },

    methods: {

        translateField,

        keyUp (event) {

            if(event.keyCode === 27) {
                this.activeFilterSelect = null;
                this.region = null;
            }

        },

        clickOutside (event) {

            this.activeFilterSelect = null;

        },

        clickInside (event) {

            this.activeFilterSelect = null;

        },

        clickRegionType(type) {
            //this.selectedCity = null;
            this.regionType = type;
        },

        clickMapRegion(region) {
            let elem = document.querySelector('.embed-regions-content-context');

            if(elem && region) {
                elem.scrollIntoView({
                    behavior: 'smooth',
                });
            }
        },

        clickSearchResultItem(city) {
            this.viewMode = 'city';
            this.selectedCity = city;
            this.term = null;
        },

        popState(event) {

            this.region = null;

            if(this.getUrlParams()['region-id']) {
                this.$store.dispatch('regions/load', this.getUrlParams()['region-id']).then((region) => {
                    this.region = region;
                });
            }

        },

        getUrlParams () {
            let queryString = window.location.search;

            if(this.historyMode === 'hash') {
                queryString = window.location.hash.substring(1);
            }

            let urlParams = new URLSearchParams(queryString);
            let result = {};

            for(const [key, value] of urlParams) {

                let k = key.split('[')[0];

                if(!['cities'].includes(k)) {
                    result[k] = value;
                    continue;
                }

                if(!result[k]) {
                    result[k] = [];
                }

                result[k].push(value);

            }

            return result;
        },

        getHistoryQueryString(region) {

            let result = [];

            if(region) {
                result.push('region-id='+region.id+'&title='+encodeURIComponent(translateField(region, 'name', this.locale)));
            }

            if(this.term) {
                result.push('term='+encodeURIComponent(this.term));
            }

            result = result.join('&');

            if(!result) {
                return this.historyBase;
            }

            return this.historyBase + (this.historyMode === 'hash' ? '#' : '') + '?' + result;

        },

        async reload() {
            this.isLoading = true;

            await Promise.all([
                this.$store.dispatch('regions/loadAll'),
                this.$store.dispatch('cities/loadAll'),
                this.$store.dispatch('states/loadAll'),
                this.$store.dispatch('contacts/loadAll'),
                this.$store.dispatch('contactGroups/loadAll'),
                this.$store.dispatch('employments/loadAll'),
            ]);

            this.isLoading = false;
        },

    },

    created() {

        if(this.history && this.getUrlParams()['term']) {
            this.term = this.getUrlParams()['term'];
        }

        this.regionType = this.regionTypes[0]['id'];

        this.reload();

    },

    mounted() {
        window.addEventListener('click', this.clickOutside);
        window.addEventListener('keyup', this.keyUp);

        if(this.history) {
            window.addEventListener('popstate', this.popState);
        }

        if(!this.disableTelemetry) {
            trackDevice();
            trackPageView();
        }
    },

    beforeUnmount() {
        window.removeEventListener('click', this.clickOutside);
        window.removeEventListener('keyup', this.keyUp);
        window.removeEventListener('popstate', this.popState);
    }

};

</script>