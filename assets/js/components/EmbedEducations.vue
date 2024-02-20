<template>

    <div class="embed-educations" :class="[$env.INSTANCE_ID+'-educations', {'is-responsive': responsive}]" @click.stop="clickInside">

        <div class="embed-educations-search">

            <div class="embed-educations-search-input">
                <input type="text" :placeholder="$t('Suchbegriff', locale)" v-model="term"
                       :class="{'has-value': term}"
                       @change="changeSearchTerm()"
                       @keyup="$event.keyCode === 13 ? changeSearchTerm() : null">
                <div class="embed-educations-search-input-icon" @click.stop="term = null; changeSearchTerm()"></div>
            </div>

        </div>

        <div class="embed-educations-filters">

            <div class="embed-educations-filters-select" data-filter-type="educationTypes">

                <div class="embed-educations-filters-select-label"
                     @click.stop="clickFilterSelect('educationType')">{{ $t('Art der Weiterbildung', locale) }}</div>

                <div class="embed-educations-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'educationType'}"></div>

                <transition name="embed-educations-filters-select-options" mode="out-in">

                    <div class="embed-educations-filters-select-options" v-if="activeFilterSelect === 'educationType'">

                        <div class="embed-educations-filters-select-options-item"
                             v-for="educationType in educationTypes"
                             :class="{ 'is-selected': isFilterSelected({ type: 'educationType', entity: educationType }) }"
                             @click.stop="clickToggleFilter({ type: 'educationType', entity: educationType })">
                            {{ translateField(educationType, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-educations-filters-select" data-filter-type="languages">

                <div class="embed-educations-filters-select-label"
                     @click.stop="clickFilterSelect('language')">{{ $t('Durchführungssprache', locale) }}</div>

                <div class="embed-educations-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'language'}"></div>

                <transition name="embed-educations-filters-select-options" mode="out-in">

                    <div class="embed-educations-filters-select-options" v-if="activeFilterSelect === 'language'">

                        <div class="embed-educations-filters-select-options-item"
                             v-for="language in languages"
                             :class="{ 'is-selected': isFilterSelected({ type: 'language', entity: language }) }"
                             @click.stop="clickToggleFilter({ type: 'language', entity: language })">
                            {{ translateField(language, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-educations-filters-select" data-filter-type="locations">

                <div class="embed-educations-filters-select-label"
                     @click.stop="clickFilterSelect('location')">{{ $t('Durchführungsort', locale) }}</div>

                <div class="embed-educations-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'location'}"></div>

                <transition name="embed-educations-filters-select-options" mode="out-in">

                    <div class="embed-educations-filters-select-options" v-if="activeFilterSelect === 'location'">

                        <div class="embed-educations-filters-select-options-item"
                             v-for="location in locations"
                             :class="{ 'is-selected': isFilterSelected({ type: 'location', entity: location }) }"
                             @click.stop="clickToggleFilter({ type: 'location', entity: location })">
                            {{ translateField(location, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-educations-filters-list">

                <div class="embed-educations-filters-list-item"
                     v-for="filter in filters"
                     @click.stop="clickToggleFilter(filter)">{{ translateField(filter.entity, 'name', locale) }}</div>

            </div>

        </div>

        <transition name="embed-educations-list" mode="out-in">

            <div class="embed-educations-list" v-if="!isLoading">

                <div class="embed-educations-list-item"
                     v-for="education in educations" :id="'education-'+education.id"
                     :class="{'is-draft': education.isPublic !== true}"
                     @click.stop="clickShowEducation(education)">

                    <div class="embed-educations-list-item-header" v-if="!disableThumbnails">

                        <div class="embed-educations-list-item-header-image" v-if="education.images.length" :style="{
                            backgroundImage: 'url('+$env.HOST+'/api/v1/files/view/'+ education.images[0].id +'.' + education.images[0].extension+')'
                        }"></div>

                        <div class="embed-educations-list-item-header-image" v-else></div>

                    </div>

                    <div class="embed-educations-list-item-content">

                        <h3 class="embed-educations-list-item-content-title">
                            {{ translateField(education, 'name', locale) }}
                        </h3>

                        <p class="embed-educations-list-item-content-description">
                            {{ translateField(education, 'organizer', locale) }}
                        </p>

                        <div class="embed-educations-list-item-content-tags">

                            <div class="embed-educations-list-item-content-tags-item"
                                 v-for="educationType in education.educationTypes.filter(e => getEducationTypeById(e.id))">
                                {{ translateField(getEducationTypeById(educationType.id), 'name', locale) }}
                            </div>

                            <div class="embed-educations-list-item-content-tags-item"
                                 v-for="language in education.languages.filter(e => getLanguageById(e.id))">
                                {{ translateField(getLanguageById(language.id), 'name', locale) }}
                            </div>

                            <div class="embed-educations-list-item-content-tags-item"
                                 v-for="location in education.locations.filter(e => getLocationById(e.id))">
                                {{ translateField(getLocationById(location.id), 'name', locale) }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </transition>

        <transition name="embed-educations-overlay" mode="out-in">

            <div class="embed-educations-overlay" v-if="education" @click="clickHideEducation()">

                <EmbedEducationsView :education="education" :locale="locale" @click.stop
                                   @clickClose="clickHideEducation()"></EmbedEducationsView>

            </div>

        </transition>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import EmbedEducationsView from './EmbedEducationsView';
import {track, trackDevice, trackPageView} from '../utils/logger';

export default {

    components: {
        EmbedEducationsView,
    },

    data() {
        return {
            isLoading: false,
            educations: [],
            term: '',
            filters: [],
            activeFilterSelect: null,
            education: null,
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
        disableThumbnails () {
            return this.$clientOptions?.disableThumbnails || false;
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
        ...mapState({
            educationTypes: function (state) {
                return state.educationTypes.all
                    .filter(e => !e.context || e.context === 'education')
                    .map(this.$clientOptions?.middleware?.mapEducationTypes || (e => e))
                    .filter(this.$clientOptions?.middleware?.educationTypes || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortEducationTypes || ((a, b) => a.position - b.position));
            },
            languages: function (state) {
                return state.languages.all
                    .filter(e => !e.context || e.context === 'education')
                    .map(this.$clientOptions?.middleware?.mapLanguages || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterLanguages || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortLanguages || ((a, b) => a.position - b.position));
            },
            locations: function (state) {
                return state.locations.all
                    .filter(e => !e.context || e.context === 'education')
                    .map(this.$clientOptions?.middleware?.mapLocations || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterLocations  || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortLocations  || ((a, b) => a.position - b.position));
            },
        }),
        ...mapGetters({
            getEducationTypeById: 'educationTypes/getById',
            getLanguageById: 'languages/getById',
            getLocationById: 'locations/getById',
        }),
    },

    methods: {

        translateField,

        keyUp (event) {

            if(event.keyCode === 27) {
                this.activeFilterSelect = null;
                this.education = null;
            }

        },

        clickOutside (event) {

            this.activeFilterSelect = null;

        },

        clickInside (event) {

            this.activeFilterSelect = null;

        },

        getFilterParams() {

            let params = {};
            params.term = this.term;

            let filters = [...this.filters, ...(this.fixedFilters || [])];

            for(let filter of filters) {
                params[filter.type] = [];
            }

            for(let filter of filters) {
                params[filter.type].push(filter.entity?.id || filter.entity?.name);
            }

            return params;

        },

        clickFilterSelect(name) {

            if(this.activeFilterSelect === name) {

                if(!this.disableTelemetry) {
                    track('Education Filter', 'Hide', name);
                }

                return this.activeFilterSelect = null;
            }

            if(!this.disableTelemetry) {
                track('Education Filter', 'Show', name);
            }

            this.activeFilterSelect = name;

        },

        clickToggleFilter(filter) {
            this.activeFilterSelect = null;

            let index = this.filters.findIndex(e => e.type === filter.type && e.entity.id === filter.entity.id);

            if(index !== -1) {

                this.filters.splice(index, 1);
                this.reload();

                if(this.history) {
                    window.history.replaceState(null, null, this.getHistoryQueryString());
                }

                if(!this.disableTelemetry) {
                    track('Education Filter', 'Disable', {
                        type: filter.type,
                        id: filter.entity.id,
                    });
                }

                return;

            }

            this.filters.push(filter);

            this.reload();

            if(this.history) {
                window.history.replaceState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Education Filter', 'Enable', {
                    type: filter.type,
                    id: filter.entity.id,
                });
            }
        },

        isFilterSelected(filter) {
            return this.filters.find(e => e.type === filter.type && e.entity.id === filter.entity.id);
        },

        changeSearchTerm() {

            this.reload();

            if(this.history) {
                window.history.replaceState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Education Search', 'Change', this.term);
            }

        },

        clickShowEducation(education) {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString(education));
            }

            if(!this.disableTelemetry) {
                track('Education Navigation', 'Show Education', {
                    id: education.id,
                    name: translateField(education, 'name', this.locale),
                });
            }

            this.education = education;

        },

        clickHideEducation() {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Education Navigation', 'Hide Education', {
                    id: this.education.id,
                    name: translateField(this.education, 'name', this.locale),
                });
            }

            this.education = null;

        },

        popState(event) {

            this.education = null;

            if(this.getUrlParams()['education-id']) {
                this.$store.dispatch('educations/load', this.getUrlParams()['education-id']).then((education) => {
                    this.education = education;
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

                if(!['educationTypes', 'languages', 'locations'].includes(k)) {
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

        getHistoryQueryString(education) {

            let result = [];

            if(education) {
                result.push('education-id='+education.id+'&name='+encodeURIComponent(translateField(education, 'name', this.locale)));
            }

            if(this.term) {
                result.push('term='+encodeURIComponent(this.term));
            }

            for(let filter of this.filters) {
                result.push(filter.type+'s[]='+encodeURIComponent(translateField(filter.entity, 'name', this.locale)))
            }

            result = result.join('&');

            if(!result) {
                return this.historyBase;
            }

            return this.historyBase + (this.historyMode === 'hash' ? '#' : '') + '?' + result;

        },

        applyFiltersFromUrlParameters() {

            this.term = this.getUrlParams()['term'];

            let filters = [];

            ['educationType', 'language', 'location'].forEach((key) => {

                let collection = key+'s';

                if(!this.getUrlParams()[key+'s']) {
                    return;
                }

                this.getUrlParams()[key+'s'].forEach((f) => {
                    let entity = this[collection].find(e => e.id === f || e.name === f || translateField(e, 'name', this.locale) === f);

                    if(!entity) {
                        return;
                    }

                    filters.push({
                        type: key,
                        entity: entity,
                    });
                });

            });

            if(filters.length) {
                this.filters = filters;
                this.reload();
            }

        },

        reload() {
            this.isLoading = true;

            return this.$store.dispatch('educations/loadFiltered', this.getFilterParams()).then((educations) => {

                this.educations = [
                    ...educations,
                ];

                this.isLoading = false;

            });
        },

    },

    created() {
        this.filters = this.$clientOptions?.defaultFilters || [];

        if(this.history && this.getUrlParams()['term']) {
            this.term = this.getUrlParams()['term'];
        }

        this.reload();

        Promise.all([
            this.$store.dispatch('educationTypes/loadAll'),
            this.$store.dispatch('languages/loadAll'),
            this.$store.dispatch('locations/loadAll'),
        ]).then(() => {

            this.filters = this.filters
                .filter((filter) => {
                    return ['educationType', 'language', 'location'].includes(filter.type);
                })
                .map((filter) => {
                    return {
                        type: filter.type,
                        entity: {
                            ...this[filter.type+'s'].find(e => e.id === filter.entity.id),
                        },
                    }
                });

            if(this.history) {
                this.applyFiltersFromUrlParameters();
            }

        });

    },

    mounted() {
        window.addEventListener('click', this.clickOutside);
        window.addEventListener('keyup', this.keyUp);

        if(this.history && this.getUrlParams()['education-id']) {
            this.$store.dispatch('educations/load', this.getUrlParams()['education-id']).then((education) => {
                this.education = education;
            });
        }

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