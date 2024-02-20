<template>

    <div class="event-collection-component">

        <div class="event-collection-component-form">

            <div class="event-collection-component-form-header">

                <h3>Agenda Kollektion erstellen</h3>

                <div class="event-collection-component-form-header-actions">
                    <a class="button warning" @click="clickCancel()">Abbrechen</a>
                    <a class="button primary" @click="clickSave()">Speichern</a>
                </div>

            </div>

            <div class="event-collection-component-form-section">

                <div class="row">
                    <div class="col-md-6">
                        <label for="title">Name</label>
                        <input id="title" type="text" class="form-control" v-model="eventCollection.title">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label for="description">Beschreibung</label>
                        <textarea name="description" id="description" class="form-control" rows="4" v-model="eventCollection.description"></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <a class="button warning"
                           v-if="!eventCollection.isDynamic"
                           @click="clickIsDynamic()">Dynamisches Suchprofil aktivieren</a>
                        <a class="button primary"
                           v-else
                           @click="clickIsDynamic()">Dynamisches Suchprofil deaktivieren</a>
                    </div>
                </div>

            </div>

            <div class="events-component">

                <div class="events-component-content events-component-content-selection" v-if="!eventCollection.isDynamic">

                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titel</th>
                                <th>Von</th>
                                <th>Bis</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody v-if="!eventCollection.selection.length">
                            <tr>
                                <td colspan="5"><em>Keine Projekte ausgewählt.</em></td>
                            </tr>
                        </tbody>
                        <draggable v-else :list="eventCollection.selection" :tag="'tbody'" item-key="id">
                            <template #item="{element}">
                                <tr>
                                    <td>{{ element.id }}</td>
                                    <td>{{ getEventById(element.id) ? translateField(getEventById(element.id), 'title') : '' }}</td>
                                    <td>{{ getEventById(element.id) ? formatDateTime(getEventById(element.id).startDate) : '' }}</td>
                                    <td>{{ getEventById(element.id) ? formatDateTime(getEventById(element.id).endDate) : '' }}</td>
                                    <td style="text-align: right">
                                        <a @click="clickEvent(element)" class="error" style="cursor: pointer">Entfernen</a>
                                    </td>
                                </tr>
                            </template>
                        </draggable>
                    </table>

                </div>

                <div class="events-component-filter">

                    <transition name="fade" mode="out-in">
                        <div class="loading-indicator" v-if="isLoading('events')" @change="changeForm()"></div>
                    </transition>

                    <div class="row" v-if="!eventCollection.isDynamic">
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label for="term">Suchbegriff</label>
                                <input id="term" type="text" class="form-control" v-model="term" @change="changeForm()">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="type">Typ</label>
                                <div class="select-wrapper">
                                    <select id="type" class="form-control" @change="addFilter({type: 'type', value: $event.target.value}); $event.target.value = null;">
                                        <option></option>
                                        <option value="external">Externe Veranstaltung</option>
                                        <option value="regiosuisse">regiosuisse Veranstaltung</option>
                                        <optgroup label="regiosuisse">
                                            <option value="fsk">FSK Sitzung</option>
                                            <option value="cafe-r">caféR</option>
                                            <option value="einstiegskurs">Einstiegskurs</option>
                                            <option value="konferenz">Konferenz</option>
                                            <option value="wissenschaftsforum">Wissenschaftsforum</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-1">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <div class="select-wrapper">
                                    <select id="status" class="form-control" @change="addFilter({type: 'status', value: $event.target.value}); $event.target.value = null;">
                                        <option></option>
                                        <option value="current">Aktuell</option>
                                        <option value="archive">Archiv</option>
                                    </select>
                                </div>
                            </div>
                        </div>
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
                                <label for="language">Durchführungssprache</label>
                                <div class="select-wrapper">
                                    <select id="language" class="form-control" @change="addFilter({type: 'language', value: $event.target.value}); $event.target.value = null;">
                                        <option></option>
                                        <option v-for="language in languages.filter(language => !language.context || language.context === 'event')">{{language.name}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="location">Durchführungsort</label>
                                <div class="select-wrapper">
                                    <select id="location" class="form-control" @change="addFilter({type: 'location', value: $event.target.value}); $event.target.value = null;">
                                        <option></option>
                                        <option v-for="location in locations.filter(location => !location.context || location.context === 'event')">{{location.name}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="events-component-filter-tags">
                        <div class="tag" v-for="filter of eventCollection.filters" @click="removeFilter({type: filter.type, value: filter.value})">
                            <strong v-if="filter.type === 'type'">Typ:</strong>
                            <strong v-if="filter.type === 'topic'">Thema:</strong>
                            <strong v-if="filter.type === 'language'">Durchführungssprache:</strong>
                            <strong v-if="filter.type === 'location'">Durchführungsort:</strong>
                            <strong v-if="filter.type === 'status'">Status:</strong>
                            {{ filter.value }}
                        </div>
                    </div>

                </div>

                <div class="events-component-content">

                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titel</th>
                                <th>Von</th>
                                <th>Bis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="event in unselectedEvents"
                                class="clickable"
                                @click="clickEvent(event)">
                                <td>{{ event.id }}</td>
                                <td>{{ translateField(event, 'title') }}</td>
                                <td>{{ formatDateTime(event.startDate) }}</td>
                                <td>{{ formatDateTime(event.endDate) }}</td>
                            </tr>
                        </tbody>
                    </table>

                </div>

            </div>

        </div>

    </div>

</template>

<script>
    import {mapGetters, mapState} from 'vuex';
    import draggable from 'vuedraggable';
    import { translateField } from '../utils/filters';
    import moment from 'moment';

    export default {
        data() {
            return {
                previewEvent: null,
                term: '',
                eventCollection: {
                    title: '',
                    description: '',
                    isDynamic: false,
                    selection: [],
                    filters: [],
                }
            };
        },
        components: {
            draggable,
        },
        computed: {
            ...mapState({
                events: state => state.events.filtered,
                topics: state => state.topics.all,
                languages: state => state.languages.all,
                locations: state => state.locations.all,
                selectedEventCollection: state => state.eventCollections.eventCollection,
            }),
            ...mapGetters({
                isLoading: 'loaders/isLoading',
                getEventById: 'events/getById',
            }),
            unselectedEvents() {
                return this.events.filter((event) => {
                    return this.eventCollection.isDynamic || !this.isSelected(event);
                });
            },
        },
        methods: {
            translateField,
            changeForm () {
                this.reloadEvents();
            },
            getFilterParams () {
                let params = {};
                params.term = this.term;

                this.eventCollection.filters.forEach((filter) => {
                    if(filter.type === 'status' && filter.value === 'archive') {
                        params.archive = '1';
                        return;
                    }
                    if(filter.type === 'status' && filter.value === 'current') {
                        params.archive = '0';
                        return;
                    }
                    if(!params[filter.type]) {
                        params[filter.type] = [];
                    }
                    params[filter.type].push(filter.value);
                });

                return params;
            },
            reloadEvents () {
                return this.$store.dispatch('events/loadFiltered', this.getFilterParams());
            },
            addFilter (filter) {
                if(!filter.value) {
                    return;
                }
                if(this.eventCollection.filters.filter(f => f.type === filter.type).find(f => f.value === filter.value)) {
                    return;
                }
                this.eventCollection.filters.push(filter);
                this.changeForm();
            },
            removeFilter (filter) {
                let f = this.eventCollection.filters.filter(f => f.type === filter.type).find(f => f.value === filter.value);
                if(f) {
                    this.eventCollection.filters.splice(this.eventCollection.filters.indexOf(f), 1);
                }
                this.changeForm();
            },
            clickEvent (event) {
                if(this.eventCollection.isDynamic) {
                    return false;
                }
                if(this.isSelected(event)) {
                    let index = this.eventCollection.selection.findIndex((obj) => obj.id === event.id);

                    if(index >= 0) {
                        return this.eventCollection.selection.splice(index, 1);
                    }
                }
                this.eventCollection.selection.push({id: event.id});
            },
            isSelected (event) {
                if(this.eventCollection.isDynamic) {
                    return false;
                }
                return this.eventCollection.selection.find(p => event.id === p.id);
            },
            clickIsDynamic() {
                this.eventCollection.isDynamic = !this.eventCollection.isDynamic;
                this.term = '';
                if(!this.eventCollection.isDynamic) {
                    this.eventCollection.filters = [];
                }
                this.reloadEvents();
            },
            clickSave () {
                if(!this.eventCollection.title.trim()) {
                    return alert('Geben Sie bitte einen Titel an um die Kollektion zu speichern.');
                }

                if(this.eventCollection.id) {
                    return this.$store.dispatch('eventCollections/update', this.eventCollection).then(() => {
                        this.$router.push('/event-collections');
                    });
                }

                this.$store.dispatch('eventCollections/create', this.eventCollection).then(() => {
                    this.$router.push('/event-collections');
                });
            },
            clickCancel () {
                this.$router.push('/event-collections');
            },
            clickPreview (event) {
                this.$store.dispatch('events/load', event.id).then(() => {
                    this.previewEvent = this.$store.state.events.event;
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
            reload() {
                if(this.$route.params.id) {
                    this.$store.commit('eventCollections/set', {});
                    this.$store.dispatch('eventCollections/load', this.$route.params.id).then(() => {
                        this.eventCollection = {...this.selectedEventCollection};
                        this.reloadEvents();
                        this.$store.dispatch('events/loadAll');
                    });
                }

                this.reloadEvents();
            },
        },
        created () {
            this.reload();
        }
    }
</script>