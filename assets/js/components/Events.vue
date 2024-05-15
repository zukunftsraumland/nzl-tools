<template>

    <div class="events-component">

        <div class="events-component-title">

            <h2>Agenda</h2>

            <transition name="fade" mode="out-in">
                <div class="loading-indicator" v-if="isLoading('events')"></div>
            </transition>

            <div class="events-component-title-actions">
                <router-link :to="'/events/add'" class="button primary">Neuen Eintrag erstellen</router-link>
            </div>

        </div>

        <div class="events-component-filter">

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
                        <label for="topic">Thema</label>
                        <div class="select-wrapper">
                            <select id="topic" class="form-control" @change="addFilter({type: 'topic', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="topic in topics.filter(topic => !topic.context || topic.context === 'event')">{{topic.name}}</option>
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
                                <option v-for="language in languages.filter(language => !language.context || language.context === 'event')">{{language.name}}</option>
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
                                <option v-for="location in locations.filter(location => !location.context || location.context === 'event')">{{location.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="events-component-filter-tags">
                <div class="tag" v-for="filter of filters" @click="removeFilter({type: filter.type, value: filter.value})">
                    <strong v-if="filter.type === 'topic'">Thema:</strong>
                    <strong v-if="filter.type === 'language'">Durchführungssprache:</strong>
                    <strong v-if="filter.type === 'location'">Durchführungsort:</strong>
                    {{filter.value}}
                </div>
            </div>

        </div>

        <div class="events-component-content">

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titel</th>
                        <th>Thema</th>
                        <th>Durchführungssprache</th>
                        <th>Durchführungsort</th>
                        <th>Von</th>
                        <th>Bis</th>
                        <th>Hervorheben</th>
                    </tr>
                </thead>
                <tbody v-if="!events.length && isLoading('events')">
                    <tr>
                        <td colspan="11"><em>Einträge werden geladen...</em></td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr v-for="event in events"
                        class="clickable"
                        :class="{'warning': !event.isPublic}"
                        @click="clickEvent(event)">
                        <td>{{ event.id }}</td>
                        <td>{{ translateField(event, 'title', 'de') }}</td>
                        <td>{{ formatOneToMany(event.topics, getTopicById) }}</td>
                        <td>{{ formatOneToMany(event.languages, getLanguageById) }}</td>
                        <td>{{ formatOneToMany(event.locations, getLocationById) }}</td>
                        <td>{{ formatDateTime(event.startDate) }}</td>
                        <td>{{ formatDateTime(event.endDate) }}</td>
                        <td :class="{'success': (event.isPromotedDE || event.isPromotedFR || event.isPromotedIT)}">
                            <template v-if="event.isPromotedDE">DE </template>
                            <template v-if="event.isPromotedFR">FR </template>
                            <template v-if="event.isPromotedIT">IT </template>
                        </td>
                    </tr>
                </tbody>
            </table>

            <br><a @click="clickLoadMore()" class="button" v-if="!isLoadedFully">Mehr Einträge laden</a>

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
                events: [],
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
                languages: state => state.languages.all,
                locations: state => state.locations.all,
            }),
            ...mapGetters({
                isLoading: 'loaders/isLoading',
                getEventById: 'events/getById',
                getTopicById: 'topics/getById',
                getLanguageById: 'languages/getById',
                getLocationById: 'locations/getById',
            }),
        },
        methods: {
            changeForm () {
                this.saveFilter();
                this.reloadEvents();
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

                return params;
            },
            reloadEvents () {
                this.isLoadedFully = false;
                this.offset = 0;
                return this.$store.dispatch('events/loadFiltered', this.getFilterParams()).then((events) => {
                    this.events = [
                        ...events,
                    ];
                });
            },
            clickLoadMore () {
                this.offset += this.limit;
                this.$store.dispatch('events/loadFiltered', this.getFilterParams()).then((events) => {
                    if(!events.length) {
                        this.isLoadedFully = true;
                    }
                    this.events = [
                        ...this.events,
                        ...events,
                    ];
                });
            },
            clickEvent (event) {
                this.$router.push({
                    path: '/events/'+event.id+'/edit'
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
                window.sessionStorage.setItem('regiosuisse.events.filters', JSON.stringify(this.filters));
                window.sessionStorage.setItem('regiosuisse.events.term', this.term);
            },
            loadFilter () {
                this.filters = JSON.parse(window.sessionStorage.getItem('regiosuisse.events.filters') || '[]');
                this.term = window.sessionStorage.getItem('regiosuisse.events.term') || '';
            },
            translateField,
        },
        created () {
            this.loadFilter();
            this.reloadEvents();
        },
    }
</script>