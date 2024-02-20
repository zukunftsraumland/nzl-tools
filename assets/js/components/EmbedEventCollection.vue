<template>

    <div class="embed-event-collection" :class="[$env.INSTANCE_ID+'-event-collection', {'is-responsive': responsive}]">

        <transition name="embed-event-collection-list" mode="out-in">

            <div class="embed-event-collection-list" v-if="!isLoading">

                <div class="embed-event-collection-list-item"
                     v-for="event in events" :id="'event-'+event.id"
                     :class="{'is-draft': event.isPublic !== true}"
                     @click.stop="clickShowEvent(event)">

                    <div class="embed-event-collection-list-item-header">

                        <div class="embed-event-collection-list-item-header-image" v-if="event.images.length" :style="{
                            backgroundImage: 'url('+$env.HOST+'/api/v1/files/view/'+ event.images[0].id +'.' + event.images[0].extension+')'
                        }"></div>

                        <div class="embed-event-collection-list-item-header-image" v-else></div>

                    </div>

                    <div class="embed-event-collection-list-item-content">

                        <h3 class="embed-event-collection-list-item-content-title">
                            {{ translateField(event, 'title', locale) }}
                        </h3>

                        <h4 class="embed-event-collection-list-item-content-subtitle" v-if="event.startDate && event.endDate">
                            {{ $helpers.formatDateRange(event.startDate, event.endDate) }}
                        </h4>

                        <p class="embed-event-collection-list-item-content-description">
                            {{ $helpers.textExcerpt(translateField(event, 'description', locale) || $helpers.stripHTML(translateField(event, 'text', locale)), 168, '...') }}
                        </p>

                        <div class="embed-event-collection-list-item-content-tags">

                            <div class="embed-event-collection-list-item-content-tags-item"
                                 v-for="topic in event.topics.filter(e => getTopicById(e.id))">
                                {{ translateField(getTopicById(topic.id), 'name', locale) }}
                            </div>

                            <div class="embed-event-collection-list-item-content-tags-item"
                                 v-for="language in event.languages.filter(e => getLanguageById(e.id))">
                                {{ translateField(getLanguageById(language.id), 'name', locale) }}
                            </div>

                            <div class="embed-event-collection-list-item-content-tags-item"
                                 v-for="location in event.locations.filter(e => getLocationById(e.id))">
                                {{ translateField(getLocationById(location.id), 'name', locale) }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </transition>

        <transition name="embed-event-collection-overlay" mode="out-in">

            <div class="embed-event-collection-overlay" v-if="event" @click="clickHideEvent()">

                <EmbedEventsView :event="event" :locale="locale" @click.stop
                                   @clickClose="clickHideEvent()"></EmbedEventsView>

            </div>

        </transition>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import EmbedEventsView from './EmbedEventsView';
import {track} from '../utils/logger';
import api from '../api';

export default {

    components: {
        EmbedEventsView,
    },

    data() {
        return {
            isLoading: false,
            events: [],
            event: null,
        };
    },

    computed: {
        locale () {
            return this.$clientOptions?.locale || 'de';
        },
        responsive () {
            return this.$clientOptions?.responsive ?? true;
        },
        collectionId () {
            return this.$clientOptions?.collectionId;
        },
        disableTelemetry () {
            return this.$clientOptions?.disableTelemetry || false;
        },
        ...mapState({
            topics: state => state.topics.all,
            languages: state => state.languages.all,
            locations: state => state.locations.all,
        }),
        ...mapGetters({
            getTopicById: 'topics/getById',
            getLanguageById: 'languages/getById',
            getLocationById: 'locations/getById',
        }),
    },

    methods: {

        translateField,

        keyUp (event) {

            if(event.keyCode === 27) {
                this.event = null;
            }

        },

        clickShowEvent(event) {

            if(!this.disableTelemetry) {
                track('Event Collection Navigation', 'Show Event', {
                    id: event.id,
                    title: translateField(event, 'title', this.locale),
                    collectionId: this.collectionId,
                });
            }

            this.event = event;

        },

        clickHideEvent() {

            if(!this.disableTelemetry) {
                track('Event Collection Navigation', 'Hide Event', {
                    id: this.event.id,
                    title: translateField(this.event, 'title', this.locale),
                    collectionId: this.collectionId,
                });
            }

            this.event = null;

        },

        reload() {
            this.isLoading = true;

            return api.eventCollections.getPublic(this.collectionId).then((response) => {

                let collection = response.data;

                this.events = [
                    ...collection.events,
                ];

                this.isLoading = false;

                if(!this.disableTelemetry) {
                    track('Event Collection', 'Load', {
                        id: this.collectionId,
                        title: collection.title,
                    });
                }

            });
        },

    },

    mounted() {
        if(!this.collectionId) {
            console.error('No collection id given!');
            return;
        }

        window.addEventListener('keyup', this.keyUp);

        this.reload();

        Promise.all([
            this.$store.dispatch('topics/loadAll'),
            this.$store.dispatch('languages/loadAll'),
            this.$store.dispatch('locations/loadAll'),
        ]).then(() => {});
    },

    beforeUnmount() {
        window.removeEventListener('keyup', this.keyUp);
    }

};

</script>