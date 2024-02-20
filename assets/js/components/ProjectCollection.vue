<template>

    <div class="project-collection-component">

        <div class="project-collection-component-form">

            <div class="project-collection-component-form-header">

                <h3>Projektkollektion erstellen</h3>

                <div class="project-collection-component-form-header-actions">
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="project-collection-component-form-section">

                <div class="row">
                    <div class="col-md-6">
                        <label for="title">Name</label>
                        <input id="title" type="text" class="form-control" v-model="projectCollection.title">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="description">Beschreibung</label>
                        <textarea name="description" id="description" class="form-control" rows="4" v-model="projectCollection.description"></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <a class="button warning"
                           v-if="!projectCollection.isDynamic"
                           @click="clickIsDynamic()">Dynamisches Suchprofil aktivieren</a>
                        <a class="button primary"
                           v-else
                           @click="clickIsDynamic()">Dynamisches Suchprofil deaktivieren</a>
                    </div>
                </div>

            </div>

            <div class="projects-component">

                <div class="projects-component-content projects-component-content-selection" v-if="!projectCollection.isDynamic">

                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titel</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody v-if="!projectCollection.selection.length">
                            <tr>
                                <td colspan="4"><em>Keine Projekte ausgewählt.</em></td>
                            </tr>
                        </tbody>
                        <draggable v-else :list="projectCollection.selection" :tag="'tbody'" item-key="id">
                            <template #item="{element}">
                                <tr>
                                    <td>{{ element.id }}</td>
                                    <td>{{ getProjectById(element.id) ? translateField(getProjectById(element.id), 'title') : '' }}</td>
                                    <td><a @click.stop="clickPreview(element)">Vorschau</a></td>
                                    <td style="text-align: right">
                                        <a @click="clickProject(element)" class="error" style="cursor: pointer">Entfernen</a>
                                    </td>
                                </tr>
                            </template>
                        </draggable>
                    </table>

                </div>

                <div class="projects-component-filter">

                    <transition name="fade" mode="out-in">
                        <div class="loading-indicator" v-if="isLoading('projects')"></div>
                    </transition>

                    <div class="row" v-if="!projectCollection.isDynamic">
                        <div class="col-sm-8">
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
                        <div class="tag" v-for="filter of projectCollection.filters" @click="removeFilter({type: filter.type, value: filter.value})">
                            <strong v-if="filter.type === 'status'">Status:</strong>
                            <strong v-if="filter.type === 'startDate'">Start:</strong>
                            <strong v-if="filter.type === 'endDate'">Ende:</strong>
                            <strong v-if="filter.type === 'topic'">Thema:</strong>
                            <strong v-if="filter.type === 'program'">Programm:</strong>
                            <strong v-if="filter.type === 'instrument'">Finanzierung:</strong>
                            <strong v-if="filter.type === 'state'">Kanton:</strong>
                            <strong v-if="filter.type === 'geographicRegion'">Geographische Region:</strong>
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
                                <th>Titel</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="project in unselectedProjects"
                                class="clickable"
                                @click="clickProject(project)">
                                <td>{{ project.id }}</td>
                                <td>{{ translateField(project, 'title') }}</td>
                                <td><a @click.stop="clickPreview(project)">Vorschau</a></td>
                            </tr>
                        </tbody>
                    </table>

                    <br><a @click="clickLoadMore()" class="button" v-if="!isLoadedFully">Mehr Projekte laden</a>

                </div>

            </div>

        </div>

        <div class="project-collection-component-overlay" v-if="previewProject" @click="previewProject = null">

            <EmbedProjectsView @click.stop @clickClose="previewProject = null"
                               :project="previewProject" :locale="'de'"></EmbedProjectsView>

        </div>

    </div>

</template>

<script>
    import {mapGetters, mapState} from 'vuex';
    import draggable from 'vuedraggable';
    import {translateField} from '../utils/filters';
    import EmbedProjectsView from './EmbedProjectsView';
    import moment from 'moment';

    export default {
        data() {
            return {
                projects: [],
                previewProject: null,
                term: '',
                projectCollection: {
                    title: '',
                    description: '',
                    isDynamic: false,
                    selection: [],
                    filters: [],
                },
                limit: 100,
                offset: 0,
                isLoadedFully: false,
            };
        },
        components: {
            draggable,
            EmbedProjectsView,
        },
        computed: {
            ...mapState({
                //projects: state => state.projects.filtered,
                topics: state => state.topics.all,
                programs: state => state.programs.all,
                instruments: state => state.instruments.all,
                states: state => state.states.all,
                geographicRegions: state => state.geographicRegions.all,
                businessSectors: state => state.businessSectors.all,
                selectedProjectCollection: state => state.projectCollections.projectCollection,
            }),
            ...mapGetters({
                isLoading: 'loaders/isLoading',
                getProjectById: 'projects/getById',
            }),
            unselectedProjects() {
                return this.projects.filter((project) => {
                    return this.projectCollection.isDynamic || !this.isSelected(project);
                });
            },
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
                this.reloadProjects();
            },
            getFilterParams () {
                let params = {};
                params.term = this.term;

                this.projectCollection.filters.forEach((filter) => {
                    if(!params[filter.type]) {
                        params[filter.type] = [];
                    }
                    params[filter.type].push(filter.value);
                });

                params.limit = this.limit;
                params.offset = this.offset;

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
            addFilter (filter) {
                if(!filter.value) {
                    return;
                }
                if(this.projectCollection.filters.filter(f => f.type === filter.type).find(f => f.value === filter.value)) {
                    return;
                }
                this.projectCollection.filters.push(filter);
                this.changeForm();
            },
            removeFilter (filter) {
                let f = this.projectCollection.filters.filter(f => f.type === filter.type).find(f => f.value === filter.value);
                if(f) {
                    this.projectCollection.filters.splice(this.projectCollection.filters.indexOf(f), 1);
                }
                this.changeForm();
            },
            formatDate(date, format = 'DD.MM.YYYY') {
                if(date && moment(date)) {
                    return moment(date).format(format);
                }
            },
            clickProject (project) {

                if(this.projectCollection.isDynamic) {
                    return false;
                }

                if(this.isSelected(project)) {
                    let index = this.projectCollection.selection.findIndex((obj) => obj.id === project.id);

                    if(index >= 0) {
                        this.projectCollection.selection.splice(index, 1);
                        return;
                    }
                }

                this.projectCollection.selection.push({id: project.id});

            },
            isSelected (project) {
                if(this.projectCollection.isDynamic) {
                    return false;
                }
                return this.projectCollection.selection.find(p => project.id === p.id);
            },
            clickIsDynamic() {
                this.projectCollection.isDynamic = !this.projectCollection.isDynamic;
                this.term = '';
                if(!this.projectCollection.isDynamic) {
                    this.projectCollection.filters = [];
                }
                this.reloadProjects();
            },
            clickSave () {
                if(!this.projectCollection.title.trim()) {
                    return alert('Geben Sie bitte einen Titel an um die Kollektion zu speichern.');
                }

                if(this.projectCollection.id) {
                    return this.$store.dispatch('projectCollections/update', this.projectCollection).then(() => {
                        this.$router.push('/project-collections');
                    });
                }

                this.$store.dispatch('projectCollections/create', this.projectCollection).then(() => {
                    this.$router.push('/project-collections');
                });
            },
            clickCancel () {
                this.$router.push('/project-collections');
            },
            clickPreview (project) {
                this.$store.dispatch('projects/load', project.id).then(() => {
                    this.previewProject = this.$store.state.projects.project;
                });
            },
            reload() {
                if(this.$route.params.id) {
                    this.$store.commit('projectCollections/set', {});
                    this.$store.dispatch('projectCollections/load', this.$route.params.id).then(() => {
                        this.projectCollection = {...this.selectedProjectCollection};
                        this.reloadProjects();
                        this.$store.dispatch('projects/loadAll');
                    });

                    return;
                }

                this.reloadProjects();
            },
        },
        created () {
            this.reload();
        }
    }
</script>