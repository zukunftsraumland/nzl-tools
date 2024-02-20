<template>

    <div class="projects-component">

        <div class="projects-component-title">

            <h2>Projekte</h2>

            <transition name="fade" mode="out-in">
                <div class="loading-indicator" v-if="isLoading('projects')"></div>
            </transition>

            <div class="projects-component-title-actions">
                <a href="/api/v1/projects.xlsx" class="button" download>XLSX</a>
                <router-link :to="'/projects/add'" class="button primary">Neuen Eintrag erstellen</router-link>
            </div>

        </div>

        <div class="projects-component-filter">

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="term">Suchbegriff</label>
                        <input id="term" type="text" class="form-control" v-model="term" @change="changeForm()">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <div class="select-wrapper">
                            <select id="status" class="form-control" @change="addFilter({type: 'status', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option :value="'public'">Öffentlich</option>
                                <option :value="'draft'">Entwurf</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_START_DATE">
                    <div class="form-group">
                        <label for="startDate">Start (Jahr)</label>
                        <div class="select-wrapper">
                            <select id="startDate" class="form-control" @change="addFilter({type: 'startDate', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="year in years" :value="year+'-01-01'">{{ year }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_END_DATE">
                    <div class="form-group">
                        <label for="endDate">Ende (Jahr)</label>
                        <div class="select-wrapper">
                            <select id="endDate" class="form-control" @change="addFilter({type: 'endDate', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="year in years" :value="year+'-01-01'">{{ year }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_TOPICS">
                    <div class="form-group">
                        <label for="topic">Thema</label>
                        <div class="select-wrapper">
                            <select id="topic" class="form-control" @change="addFilter({type: 'topic', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="topic in topics.filter(topic => !topic.context || topic.context === 'project')">{{topic.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_PROGRAMS">
                    <div class="form-group">
                        <label for="program">Programm</label>
                        <div class="select-wrapper">
                            <select id="program" class="form-control" @change="addFilter({type: 'program', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="program in programs.filter(program => !program.context || program.context === 'project')">{{program.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_INSTRUMENTS">
                    <div class="form-group">
                        <label for="instrument">Finanzierung</label>
                        <div class="select-wrapper">
                            <select id="instrument" class="form-control" @change="addFilter({type: 'instrument', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="instrument in instruments.filter(instrument => !instrument.context || instrument.context === 'project')">{{instrument.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_STATES">
                    <div class="form-group">
                        <label for="state">Kanton</label>
                        <div class="select-wrapper">
                            <select id="state" class="form-control" @change="addFilter({type: 'state', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="state in states.filter(state => !state.context || state.context === 'project')">{{state.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_GEOGRAPHIC_REGIONS">
                    <div class="form-group">
                        <label for="geographicRegion">Geographische Region</label>
                        <div class="select-wrapper">
                            <select id="geographicRegion" class="form-control" @change="addFilter({type: 'geographicRegion', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="geographicRegion in geographicRegions.filter(geographicRegion => !geographicRegion.context || geographicRegion.context === 'project')">{{geographicRegion.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3" v-if="$env.PROJECTS_ENABLE_BUSINESS_SECTORS">
                    <div class="form-group">
                        <label for="businessSector">Geschäftsfeld</label>
                        <div class="select-wrapper">
                            <select id="businessSector" class="form-control" @change="addFilter({type: 'businessSector', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="businessSector in businessSectors.filter(businessSector => !businessSector.context || businessSector.context === 'project')">{{businessSector.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="projects-component-filter-tags">
                <div class="tag" v-for="filter of filters" @click="removeFilter({type: filter.type, value: filter.value})">
                    <strong v-if="filter.type === 'status'">Status:</strong>
                    <strong v-if="filter.type === 'startDate'">Start:</strong>
                    <strong v-if="filter.type === 'endDate'">Ende:</strong>
                    <strong v-if="filter.type === 'topic'">Thema:</strong>
                    <strong v-if="filter.type === 'program'">Programm:</strong>
                    <strong v-if="filter.type === 'instrument'">Finanzierung:</strong>
                    <strong v-if="filter.type === 'state'">Kanton:</strong>
                    <strong v-if="filter.type === 'geographicRegion'">Geographische Region:</strong>
                    <strong v-if="filter.type === 'businessSector'">Geschäftsfeld:</strong>
                    <template v-if="['startDate', 'endDate'].includes(filter.type)">
                        &nbsp;{{ formatDate(filter.value, 'YYYY') }}
                    </template>
                    <template v-else-if="['status'].includes(filter.type)">
                        &nbsp;{{ filter.value === 'public' ? 'Öffentlich' : 'Entwurf' }}
                    </template>
                    <template v-else>
                        &nbsp;{{ filter.value }}
                    </template>
                </div>
            </div>

        </div>

        <div class="projects-component-content">

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Titel</th>
                        <th v-if="$env.PROJECTS_ENABLE_START_DATE">Start</th>
                        <th v-if="$env.PROJECTS_ENABLE_END_DATE">Ende</th>
                        <th v-if="$env.PROJECTS_ENABLE_TOPICS">Themen</th>
                        <th v-if="$env.PROJECTS_ENABLE_PROGRAMS">Programm</th>
                        <th v-if="$env.PROJECTS_ENABLE_INSTRUMENTS">Finanzierung</th>
                        <th v-if="$env.PROJECTS_ENABLE_STATES">Kanton</th>
                        <th v-if="$env.PROJECTS_ENABLE_BUSINESS_SECTORS">Geschäftsfelder</th>
                        <th>Erstellt</th>
                        <th>Geändert</th>
                    </tr>
                </thead>
                <tbody v-if="!projects.length && isLoading('projects')">
                    <tr>
                        <td colspan="11"><em>Projekte werden geladen...</em></td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr v-for="project in projects"
                        class="clickable"
                        :class="{'warning': !project.isPublic}"
                        @click="clickProject(project)">
                        <td>{{ project.id }}</td>
                        <td>{{ project.projectCode || '-' }}</td>
                        <td>{{ translateField(project, 'title') }}</td>
                        <td v-if="$env.PROJECTS_ENABLE_START_DATE">{{ project.startDate ? project.startDate.substr(0, 4) : '' }}</td>
                        <td v-if="$env.PROJECTS_ENABLE_END_DATE">{{ project.endDate ? project.endDate.substr(0, 4) : '' }}</td>
                        <td v-if="$env.PROJECTS_ENABLE_TOPICS">{{ formatOneToMany(project.topics, getTopicById) }}</td>
                        <td v-if="$env.PROJECTS_ENABLE_PROGRAMS">{{ formatOneToMany(project.programs, getProgramById) }}</td>
                        <td v-if="$env.PROJECTS_ENABLE_INSTRUMENTS">{{ formatOneToMany(project.instruments, getInstrumentById) }}</td>
                        <td v-if="$env.PROJECTS_ENABLE_STATES">{{ formatOneToMany(project.states, getStateById) }}</td>
                        <td v-if="$env.PROJECTS_ENABLE_BUSINESS_SECTORS">{{ formatOneToMany(project.businessSectors, getBusinessSectorById) }}</td>
                        <td>{{ project.createdAt ? $helpers.formatDateTime(project.createdAt) : '-' }}</td>
                        <td>{{ project.updatedAt ? $helpers.formatDateTime(project.updatedAt) : '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <br><a @click="clickLoadMore()" class="button" v-if="!isLoadedFully">Mehr Projekte laden</a>

        </div>

    </div>

</template>

<script>
    import {mapGetters, mapState} from 'vuex';
    import moment from 'moment';
    import {translateField} from '../utils/filters';

    export default {
        data () {
            return {
                projects: [],
                term: '',
                filters: [],
                limit: 100,
                offset: 0,
                isLoadedFully: false,
            };
        },
        computed: {
            ...mapState({
                topics: state => state.topics.all,
                programs: state => state.programs.all,
                instruments: state => state.instruments.all,
                states: state => state.states.all,
                geographicRegions: state => state.geographicRegions.all,
                businessSectors: state => state.businessSectors.all,
            }),
            ...mapGetters({
                isLoading: 'loaders/isLoading',
                getTopicById: 'topics/getById',
                getProgramById: 'programs/getById',
                getInstrumentById: 'instruments/getById',
                getStateById: 'states/getById',
                getGeographicRegionById: 'geographicRegions/getById',
                getBusinessSectorById: 'businessSectors/getById',
            }),
            years() {
                let years = [];
                let now = moment().startOf('year');

                for(let i = 30; i > 0; i--) {
                    years.push(now.format('YYYY'));
                    now = moment(now).subtract(1, 'year');
                }

                return years;
            },
        },
        methods: {
            translateField,
            changeForm () {
                this.saveFilter();
                this.reloadProjects();
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
            reloadProjects () {
                this.isLoadedFully = false;
                this.offset = 0;
                return this.$store.dispatch('projects/loadFiltered', this.getFilterParams()).then((projects) => {
                    this.projects = [
                        ...projects,
                    ];
                });
            },
            clickLoadMore () {
                this.offset += this.limit;
                this.$store.dispatch('projects/loadFiltered', this.getFilterParams()).then((projects) => {
                    if(!projects.length) {
                        this.isLoadedFully = true;
                    }
                    this.projects = [
                        ...this.projects,
                        ...projects,
                    ];
                });
            },
            clickProject (project) {
                this.$router.push({
                    path: '/projects/'+project.id+'/edit'
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
                window.sessionStorage.setItem('regiosuisse.projects.filters', JSON.stringify(this.filters));
                window.sessionStorage.setItem('regiosuisse.projects.term', this.term);
            },
            loadFilter () {
                this.filters = JSON.parse(window.sessionStorage.getItem('regiosuisse.projects.filters') || '[]');
                this.term = window.sessionStorage.getItem('regiosuisse.projects.term') || '';
            },
        },
        created () {
            this.loadFilter();
            this.reloadProjects();
        },
    }
</script>