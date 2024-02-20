<template>

    <div class="educations-component">

        <div class="educations-component-title">

            <h2>Bildungsmöglichkeiten</h2>

            <transition name="fade" mode="out-in">
                <div class="loading-indicator" v-if="isLoading('educations')"></div>
            </transition>

            <div class="educations-component-title-actions">
                <router-link :to="'/educations/add'" class="button primary">Neuen Eintrag erstellen</router-link>
            </div>

        </div>

        <div class="educations-component-filter">

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="term">Suchbegriff</label>
                        <input id="term" type="text" class="form-control" v-model="term" @change="changeForm()">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="education-type">Art der Weiterbildung</label>
                        <div class="select-wrapper">
                            <select id="education-type" class="form-control" @change="addFilter({type: 'educationType', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="educationType in educationTypes.filter(educationType => !educationType.context || educationType.context === 'education')">{{educationType.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="languages">Durchführungssprache</label>
                        <div class="select-wrapper">
                            <select id="languages" class="form-control" @change="addFilter({type: 'language', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="language in languages.filter(language => !language.context || language.context === 'education')">{{language.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label for="locations">Durchführungsort</label>
                        <div class="select-wrapper">
                            <select id="locations" class="form-control" @change="addFilter({type: 'location', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="location in locations.filter(location => !location.context || location.context === 'education')">{{location.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="projects-component-filter-tags">
                <div class="tag" v-for="filter of filters" @click="removeFilter({type: filter.type, value: filter.value})">
                    <strong v-if="filter.type === 'educationType'">Art der Weiterbildung</strong>
                    <strong v-if="filter.type === 'language'">Durchführungssprache:</strong>
                    <strong v-if="filter.type === 'location'">Durchführungsort:</strong>
                    {{filter.value}}
                </div>
            </div>

        </div>

        <div class="educations-component-content">

            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Bezeichnung</th>
                    <th>Art der Weiterbildung</th>
                    <th>Durchführungssprache</th>
                    <th>Durchführungsort</th>
                </tr>
                </thead>
                <tbody v-if="!educations.length && isLoading('educations')">
                <tr>
                    <td colspan="11"><em>Einträge werden geladen...</em></td>
                </tr>
                </tbody>
                <tbody v-else>
                <tr v-for="education in educations"
                    class="clickable"
                    :class="{'warning': !education.isPublic}"
                    @click="clickEducation(education)">
                    <td>{{ education.id }}</td>
                    <td>{{ translateField(education, 'name', 'de') }}</td>
                    <td>{{ formatOneToMany(education.educationTypes, getEducationTypeById) }}</td>
                    <td>{{ formatOneToMany(education.languages, getLanguageById) }}</td>
                    <td>{{ formatOneToMany(education.locations, getLocationById) }}</td>
                </tr>
                </tbody>
            </table>

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
            term: '',
            filters: [],
        };
    },
    computed: {
        ...mapState({
            educations: state => state.educations.filtered,
            educationTypes: state => state.educationTypes.all,
            languages: state => state.languages.all,
            locations: state => state.locations.all,
        }),
        ...mapGetters({
            isLoading: 'loaders/isLoading',
            getEducationById: 'educations/getById',
            getEducationTypeById: 'educationTypes/getById',
            getLanguageById: 'languages/getById',
            getLocationById: 'locations/getById',
        }),
    },
    methods: {
        changeForm () {
            this.saveFilter();
            this.reloadEducations();
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

            return params;
        },
        reloadEducations () {
            return this.$store.dispatch('educations/loadFiltered', this.getFilterParams());
        },
        clickEducation (education) {
            this.$router.push({
                path: '/educations/'+education.id+'/edit'
            });
        },
        formatOneToMany (items, getter) {
            let result = [];
            items.forEach((item) => {
                result.push(getter(item.id)?.name);
            });

            return result.join(', ');
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
            window.sessionStorage.setItem('regiosuisse.educations.filters', JSON.stringify(this.filters));
            window.sessionStorage.setItem('regiosuisse.educations.term', this.term);
        },
        loadFilter () {
            this.filters = JSON.parse(window.sessionStorage.getItem('regiosuisse.educations.filters') || '[]');
            this.term = window.sessionStorage.getItem('regiosuisse.educations.term') || '';
        },
        translateField,
    },
    created () {
        this.loadFilter();
        this.reloadEducations();
    },
}
</script>