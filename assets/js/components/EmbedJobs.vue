<template>

    <div class="embed-jobs" :class="[$env.INSTANCE_ID+'-jobs', {'is-responsive': responsive}]" @click.stop="clickInside">

        <div class="embed-jobs-search">

            <div class="embed-jobs-search-input">
                <input type="text" :placeholder="$t('Suchbegriff', locale)" v-model="term"
                       :class="{'has-value': term}"
                       @change="changeSearchTerm()"
                       @keyup="$event.keyCode === 13 ? changeSearchTerm() : null">
                <div class="embed-jobs-search-input-icon" @click.stop="term = null; changeSearchTerm()"></div>
            </div>

        </div>

        <div class="embed-jobs-filters">

            <div class="embed-jobs-filters-select" data-filter-type="stints">

                <div class="embed-jobs-filters-select-label"
                     @click.stop="clickFilterSelect('stint')">{{ $t('Pensum', locale) }}</div>

                <div class="embed-jobs-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'stint'}"></div>

                <transition name="embed-jobs-filters-select-options" mode="out-in">

                    <div class="embed-jobs-filters-select-options" v-if="activeFilterSelect === 'stint'">

                        <div class="embed-jobs-filters-select-options-item"
                             v-for="stint in stints"
                             :class="{ 'is-selected': isFilterSelected({ type: 'stint', entity: stint }) }"
                             @click.stop="clickToggleFilter({ type: 'stint', entity: stint })">
                            {{ translateField(stint, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-jobs-filters-select" data-filter-type="locations">

                <div class="embed-jobs-filters-select-label"
                     @click.stop="clickFilterSelect('location')">{{ $t('Ort', locale) }}</div>

                <div class="embed-jobs-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'location'}"></div>

                <transition name="embed-jobs-filters-select-options" mode="out-in">

                    <div class="embed-jobs-filters-select-options" v-if="activeFilterSelect === 'location'">

                        <div class="embed-jobs-filters-select-options-item"
                             v-for="location in locations"
                             :class="{ 'is-selected': isFilterSelected({ type: 'location', entity: location }) }"
                             @click.stop="clickToggleFilter({ type: 'location', entity: location })">
                            {{ translateField(location, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-jobs-filters-list">

                <div class="embed-jobs-filters-list-item"
                     v-for="filter in filters"
                     @click.stop="clickToggleFilter(filter)">{{ translateField(filter.entity, 'name', locale) }}</div>

            </div>

        </div>

        <transition name="embed-jobs-list" mode="out-in">

            <div class="embed-jobs-list" v-if="!isLoading">

                <div class="embed-jobs-list-item"
                     v-for="job in jobs" :id="'job-'+job.id"
                     :class="{'is-draft': job.isPublic !== true}"
                     @click.stop="clickShowJob(job)">

                    <div class="embed-jobs-list-item-header" v-if="!disableThumbnails">

                        <div class="embed-jobs-list-item-header-image" v-if="job.images.length" :style="{
                            backgroundImage: 'url('+$env.HOST+'/api/v1/files/view/'+ job.images[0].id +'.' + job.images[0].extension+')'
                        }"></div>

                        <div class="embed-jobs-list-item-header-image" v-else></div>

                    </div>

                    <div class="embed-jobs-list-item-content">

                        <h3 class="embed-jobs-list-item-content-title">
                            {{ translateField(job, 'name', locale) }}
                        </h3>

                        <p class="embed-jobs-list-item-content-description">
                            {{ translateField(job, 'employer', locale) }}
                        </p>

                        <div class="embed-jobs-list-item-content-tags">

                            <div class="embed-jobs-list-item-content-tags-item"
                                 v-for="stint in job.stints.filter(e => getStintById(e.id))">
                                {{ translateField(getStintById(stint.id), 'name', locale) }}
                            </div>

                            <div class="embed-jobs-list-item-content-tags-item"
                                 v-for="location in job.locations.filter(e => getLocationById(e.id))">
                                {{ translateField(getLocationById(location.id), 'name', locale) }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </transition>

        <transition name="embed-jobs-overlay" mode="out-in">

            <div class="embed-jobs-overlay" v-if="job" @click="clickHideJob()">

                <EmbedJobsView :job="job" :locale="locale" @click.stop
                                   @clickClose="clickHideJob()"></EmbedJobsView>

            </div>

        </transition>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import EmbedJobsView from './EmbedJobsView';
import {track, trackDevice, trackPageView} from '../utils/logger';

export default {

    components: {
        EmbedJobsView,
    },

    data() {
        return {
            isLoading: false,
            jobs: [],
            term: '',
            filters: [],
            activeFilterSelect: null,
            job: null,
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
            locations: function (state) {
                return state.locations.all
                    .filter(e => !e.context || e.context === 'job')
                    .map(this.$clientOptions?.middleware?.mapLocations || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterLocations  || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortLocations  || ((a, b) => a.position - b.position));
            },
            stints: function (state) {
                return state.stints.all
                    .filter(e => !e.context || e.context === 'job')
                    .map(this.$clientOptions?.middleware?.mapStints || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterStints  || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortStints  || ((a, b) => a.position - b.position));
            },
        }),
        ...mapGetters({
            getLocationById: 'locations/getById',
            getStintById: 'stints/getById',
        }),
    },

    methods: {

        translateField,

        keyUp (event) {

            if(event.keyCode === 27) {
                this.activeFilterSelect = null;
                this.job = null;
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

            params.archive = 0;

            return params;

        },

        clickFilterSelect(name) {

            if(this.activeFilterSelect === name) {

                if(!this.disableTelemetry) {
                    track('Job Filter', 'Hide', name);
                }

                return this.activeFilterSelect = null;
            }

            if(!this.disableTelemetry) {
                track('Job Filter', 'Show', name);
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
                    track('Job Filter', 'Disable', {
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
                track('Job Filter', 'Enable', {
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
                track('Job Search', 'Change', this.term);
            }

        },

        clickShowJob(job) {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString(job));
            }

            if(!this.disableTelemetry) {
                track('Job Navigation', 'Show Job', {
                    id: job.id,
                    name: translateField(job, 'name', this.locale),
                });
            }

            this.job = job;

        },

        clickHideJob() {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Job Navigation', 'Hide Job', {
                    id: this.job.id,
                    name: translateField(this.job, 'name', this.locale),
                });
            }

            this.job = null;

        },

        popState(event) {

            this.job = null;

            if(this.getUrlParams()['job-id']) {
                this.$store.dispatch('jobs/load', this.getUrlParams()['job-id']).then((job) => {
                    this.job = job;
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

                if(!['locations', 'stints'].includes(k)) {
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

        getHistoryQueryString(job) {

            let result = [];

            if(job) {
                result.push('job-id='+job.id+'&name='+encodeURIComponent(translateField(job, 'name', this.locale)));
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

            ['location', 'stint'].forEach((key) => {

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

            return this.$store.dispatch('jobs/loadFiltered', this.getFilterParams()).then((jobs) => {

                this.jobs = [
                    ...jobs,
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
            this.$store.dispatch('locations/loadAll'),
            this.$store.dispatch('stints/loadAll'),
        ]).then(() => {

            this.filters = this.filters
                .filter((filter) => {
                    return ['location', 'stint'].includes(filter.type);
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

        if(this.history && this.getUrlParams()['job-id']) {
            this.$store.dispatch('jobs/load', this.getUrlParams()['job-id']).then((job) => {
                this.job = job;
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