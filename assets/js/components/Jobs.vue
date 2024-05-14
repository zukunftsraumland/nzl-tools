<template>

    <div class="jobs-component">

        <div class="jobs-component-title">

            <h2>Stellenmarkt</h2>

            <transition name="fade" mode="out-in">
                <div class="loading-indicator" v-if="isLoading('jobs')"></div>
            </transition>

            <div class="jobs-component-title-actions">
                <button class="button" @click="toggleArchive">Archiv {{ showArchive ? 'ausblenden' : 'anzeigen' }}</button>
                <router-link :to="'/jobs/add'" class="button primary">Neuen Eintrag erstellen</router-link>
            </div>

        </div>

        <div class="jobs-component-content">

            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Bezeichnung</th>
                    <th>Arbeitsort</th>
                    <th>Bewerbungsfrist</th>
                </tr>
                </thead>
                <tbody v-if="!jobs.length && isLoading('jobs')">
                <tr>
                    <td colspan="11"><em>Einträge werden geladen...</em></td>
                </tr>
                </tbody>
                <draggable v-else :list="jobs" :tag="'tbody'" item-key="id" @change="changeSort">
                    <template #item="{element}">
                        <tr class="clickable"
                            @click="clickJob(element)"
                            :class="{'warning': !element.isPublic}">
                            <td>{{ element.id }}</td>
                            <td>{{ translateField(element, 'name', 'de') }}</td>
                            <td>{{ formatOneToMany(element.locations, getLocationById) }}</td>
                            <td>{{ formatDateTime(element.applicationDeadline) }}</td>
                        </tr>
                    </template>
                </draggable>
            </table>

        </div>

        <transition name="fade" mode="in-out">

            <div class="context-bar" v-if="isSortChanged">
                <div class="context-bar-content">
                    <p v-if="!isLoading('jobs/*')">Sortierung geändert. Möchten Sie die Änderungen speichern?</p>
                    <p v-else>{{ sortChangeProgress }} von {{ jobs.length }} Positionen gespeichert...</p>
                </div>
                <template v-if="!isLoading('jobs/*')">
                    <a class="button warning" @click="clickRestoreSort()">Zurücksetzen</a>
                    <a class="button success" @click="clickSaveSort()">Speichern</a>
                </template>
            </div>

        </transition>

    </div>

</template>

<script>
import { mapState, mapGetters } from 'vuex';
import moment from 'moment';
import { translateField } from '../utils/filters';
import draggable from 'vuedraggable';

export default {
    data () {
        return {
            term: '',
            filters: [],
            isSortChanged: false,
            sortChangeProgress: 0,
            showArchive: 0,
        };
    },
    components: {
        draggable,
    },
    computed: {
        ...mapState({
            jobs: state => state.jobs.filtered,
            locations: state => state.locations.all,
        }),
        ...mapGetters({
            isLoading: 'loaders/isLoading',
            getJobById: 'jobs/getById',
            getLocationById: 'locations/getById',
        }),
    },
    methods: {
        changeForm () {
            this.saveFilter();
            this.reloadJobs();
        },
        toggleArchive() {
            this.showArchive = !this.showArchive ? 1 : 0;
            this.reloadJobs();
        },
        getFilterParams () {
            let params = {};
            params.archive = this.showArchive;
            params.term = this.term;

            this.filters.forEach((filter) => {
                if(!params[filter.type]) {
                    params[filter.type] = [];
                }
                params[filter.type].push(filter.value);
            });

            return params;
        },
        reloadJobs () {
            return this.$store.dispatch('jobs/loadFiltered', this.getFilterParams());
        },
        clickJob (job) {
            this.$router.push({
                path: '/jobs/'+job.id+'/edit'
            });
        },
        formatOneToMany (items, getter) {
            let result = [];
            items.forEach((item) => {
                result.push(getter(item.id)?.name);
            });

            return result.join(', ');
        },
        changeSort() {
            this.isSortChanged = true;
        },
        async clickSaveSort() {
            this.sortChangeProgress = 0;
            for(let key in this.jobs) {
                await this.$store.dispatch('jobs/update', {
                    ...this.jobs[key],
                    position: key,
                });
                this.sortChangeProgress++;
            }
            this.isSortChanged = false;
            this.reloadJobs();
        },
        clickRestoreSort() {
            this.isSortChanged = false;
            this.reloadJobs();
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
            window.sessionStorage.setItem('regiosuisse.jobs.filters', JSON.stringify(this.filters));
            window.sessionStorage.setItem('regiosuisse.jobs.term', this.term);
        },
        loadFilter () {
            this.filters = JSON.parse(window.sessionStorage.getItem('regiosuisse.jobs.filters') || '[]');
            this.term = window.sessionStorage.getItem('regiosuisse.jobs.term') || '';
        },
        translateField,
    },
    created () {
        this.loadFilter();
        this.reloadJobs();
    },
}
</script>