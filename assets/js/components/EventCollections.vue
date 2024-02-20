<template>

    <div class="event-collections-component">

        <div class="event-collections-component-form">

            <div class="event-collections-component-form-header">

                <h3>Agenda-Kollektionen</h3>

                <div class="event-collections-component-form-header-actions">
                    <router-link :to="'/event-collections/add'" class="button primary">Neue Kollektion erstellen</router-link>
                </div>

            </div>

        </div>

        <div class="event-collections-component-list">

            <router-link class="event-collections-component-list-item"
                         v-for="eventCollection in eventCollections"
                         tag="div"
                         :to="'/event-collections/'+eventCollection.id+'/edit'"
            >

                <div class="event-collections-component-list-item-name">
                    <h3>{{ eventCollection.title }} <span class="material-icons" v-if="eventCollection.isDynamic">auto_fix_high</span></h3>
                    <p v-if="eventCollection.description">{{ eventCollection.description }}</p>
                </div>

                <div class="event-collections-component-list-item-count" v-if="eventCollection.isDynamic">
                    {{ eventCollection.filters.length }} Filter ausgewählt (Dynamisch)
                </div>

                <div class="event-collections-component-list-item-count" v-else>
                    {{ eventCollection.selection.length }} Einträge ausgewählt
                </div>

            </router-link>

        </div>

    </div>

</template>

<script>
    import { mapState, mapGetters } from 'vuex';
    import { filterEvents } from '../utils/filters';

    export default {
        data () {
            return {};
        },
        computed: {
            ...mapState({
                events: state => state.events.all,
                eventCollections: state => state.eventCollections.all,
            }),
            ...mapGetters({
                getEventById: 'events/getById',
            }),
        },
        created () {
            this.$store.dispatch('eventCollections/loadAll');

            if(!this.events.length) {
                // this.$store.dispatch('events/loadAll');
            }
        },
        methods: {
            filterEvents (events, filters, term) {
                return filterEvents(events.map((p) => {
                    return this.getEventById(p.id);
                }).filter(p => p), filters, term);
            },
        },
    }
</script>