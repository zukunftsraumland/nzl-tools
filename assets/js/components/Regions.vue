<template>

    <div class="regions-component">

        <div class="regions-component-title">

            <h2>Regionen</h2>

            <transition name="fade" mode="out-in">
                <div class="loading-indicator" v-if="isLoading('regions')"></div>
            </transition>

            <div class="regions-component-title-actions">
                <a href="/api/v1/regions.xlsx" class="button" download>XLSX</a>
                <router-link :to="'/regions/add'" class="button primary">Neuen Eintrag erstellen</router-link>
            </div>

        </div>

        <div class="regions-component-filter">

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="term">Suchbegriff</label>
                        <input id="term" type="text" class="form-control" v-model="term" @change="changeForm()">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="type">Typ</label>
                        <div class="select-wrapper">
                            <select id="type" class="form-control" @change="addFilter({type: 'type', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="regionType in regionTypes" :value="regionType.id">{{ regionType.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="regions-component-filter-tags">
                <div class="tag" v-for="filter of filters" @click="removeFilter({type: filter.type, value: filter.value})">
                    <strong v-if="filter.type === 'type'">Typ:</strong>
                    {{filter.value}}
                </div>
            </div>

        </div>

        <div class="regions-component-content">

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Typ</th>
                        <th>Gemeinden</th>
                        <th>Kontakte</th>
                        <th>Website</th>
                        <th>Erstellt am</th>
                        <th>Aktualisiert am</th>
                    </tr>
                </thead>
                <tbody v-if="!regions.length && isLoading('regions')">
                    <tr>
                        <td colspan="11"><em>Einträge werden geladen...</em></td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr v-for="region in regions"
                        class="clickable"
                        :class="{'warning': !region.isPublic}"
                        @click="clickRegion(region)">
                        <td>{{ region.id }}</td>
                        <td>{{ region.name }}</td>
                        <td v-if="region.type">{{ regionTypes.find(regionType => regionType.id === region.type)?.name || 'Fehler' }}</td>
                        <td v-else>-</td>
                        <td v-if="region.cities.length">{{ region.cities.length }} Gemeinden</td>
                        <td v-else>-</td>
                        <td v-if="region.contacts.length">{{ region.contacts.length }} Kontakte</td>
                        <td v-else>-</td>
                        <td v-if="region.url"><a :href="region.url" :title="region.url" @click.stop target="_blank">Anzeigen</a></td>
                        <td v-else>-</td>
                        <td>{{ $helpers.formatDate(region.createdAt) }}</td>
                        <td>{{ $helpers.formatDate(region.updatedAt) }}</td>
                    </tr>
                </tbody>
            </table>

            <br><a @click="clickLoadMore()" class="button" v-if="!isLoadedFully">Mehr Regionen laden</a>

        </div>

    </div>

</template>

<script>
import { mapState, mapGetters } from 'vuex';
import moment from 'moment';
import { translateField } from '../utils/filters';

export default {
    data () {
        return {
            regions: [],
            term: '',
            filters: [],
            limit: 100,
            offset: 0,
            isLoadedFully: false,
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
        };
    },
    computed: {
        ...mapGetters({
            isLoading: 'loaders/isLoading',
            getRegionById: 'regions/getById',
        }),
    },
    methods: {
        changeForm () {
            this.saveFilter();
            this.reloadRegions();
        },
        getFilterParams () {
            let params = {};
            params.term = this.term;

            this.filters.forEach((filter) => {
                if(!params[filter.type]) {
                    params[filter.type] = [];
                }
                params[filter.type].push(filter.value);
            });

            params.limit = this.limit;
            params.offset = this.offset;
            params.orderBy = ['id'];
            params.orderDirection = ['DESC'];

            return params;
        },
        reloadRegions () {
            this.isLoadedFully = false;
            this.offset = 0;
            return this.$store.dispatch('regions/loadFiltered', this.getFilterParams()).then((regions) => {
                this.regions = [
                    ...regions,
                ];
            });
        },
        clickLoadMore () {
            this.offset += this.limit;
            this.$store.dispatch('regions/loadFiltered', this.getFilterParams()).then((regions) => {
                if(!regions.length) {
                    this.isLoadedFully = true;
                }
                this.regions = [
                    ...this.regions,
                    ...regions,
                ];
            });
        },
        clickRegion (region) {
            this.$router.push({
                path: '/regions/'+region.id+'/edit'
            });
        },
        formatDate(date, format = 'DD.MM.YYYY') {
            if(date && moment(date)) {
                return moment(date).format(format);
            }
        },
        formatDateTime(date) {
            return this.formatDate(date, 'DD.MM.YYYY HH:mm');
        },
        addFilter (filter) {
            if(!filter.value) {
                return;
            }
            if(this.filters.filter(f => f.type === filter.type).find(f => f.value === filter.value)) {
                return;
            }
            this.filters.push(filter);
            this.changeForm();
        },
        removeFilter (filter) {
            let f = this.filters.filter(f => f.type === filter.type).find(f => f.value === filter.value);
            if(f) {
                this.filters.splice(this.filters.indexOf(f), 1);
            }
            this.changeForm();
        },
        saveFilter () {
            window.sessionStorage.setItem('regiosuisse.regions.filters', JSON.stringify(this.filters));
            window.sessionStorage.setItem('regiosuisse.regions.term', this.term);
        },
        loadFilter () {
            this.filters = JSON.parse(window.sessionStorage.getItem('regiosuisse.regions.filters') || '[]');
            this.term = window.sessionStorage.getItem('regiosuisse.regions.term') || '';
        },
        translateField,
    },
    created () {
        this.loadFilter();
        this.reloadRegions();
    },
}
</script>