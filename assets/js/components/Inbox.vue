<template>

    <div class="inbox-component">

        <div class="inbox-component-section">

            <transition name="fade" mode="out-in">
                <div class="loading-indicator" v-if="isLoading('inbox')"></div>
            </transition>

            <div class="inbox-component-section-title">
                <h2>Projekte</h2>
            </div>

            <div class="inbox-component-section-content">
                <inbox-card
                        v-for="item in filterInboxItemsByType(inbox, 'project')"
                        :item="item"
                        @click="clickProject(item.id)"
                        @onDismiss="clickDismiss(item)"
                ></inbox-card>
            </div>

        </div>

        <div class="inbox-component-section" v-if="$env.PROJECTS_ENABLE_TOPICS">

            <div class="inbox-component-section-title">
                <h2>Themen</h2>
            </div>

            <div class="inbox-component-section-content">
                <inbox-card
                        v-for="item in filterInboxItemsByType(inbox, 'topic')"
                        :item="item"></inbox-card>
            </div>

        </div>

        <div class="inbox-component-section" v-if="$env.PROJECTS_ENABLE_STATES">

            <div class="inbox-component-section-title">
                <h2>Bundesland</h2>
            </div>

            <div class="inbox-component-section-content">
                <inbox-card
                        v-for="item in filterInboxItemsByType(inbox, 'state')"
                        :item="item"></inbox-card>
            </div>

        </div>

        <div class="inbox-component-section" v-if="$env.PROJECTS_ENABLE_PROGRAMS">

            <div class="inbox-component-section-title">
                <h2>Programme</h2>
            </div>

            <div class="inbox-component-section-content">
                <inbox-card
                        v-for="item in filterInboxItemsByType(inbox, 'program')"
                        :item="item"></inbox-card>
            </div>

        </div>

        <div class="inbox-component-section" v-if="$env.PROJECTS_ENABLE_INSTRUMENTS">

            <div class="inbox-component-section-title">
                <h2>Finanzierung</h2>
            </div>

            <div class="inbox-component-section-content">
                <inbox-card
                        v-for="item in filterInboxItemsByType(inbox, 'instrument')"
                        :item="item"></inbox-card>
            </div>

        </div>

        <div class="inbox-component-section" v-if="$env.PROJECTS_ENABLE_BUSINESS_SECTORS">

            <div class="inbox-component-section-title">
                <h2>Geschäftsfelder</h2>
            </div>

            <div class="inbox-component-section-content">
                <inbox-card
                        v-for="item in filterInboxItemsByType(inbox, 'businessSector')"
                        :item="item"></inbox-card>
            </div>

        </div>

    </div>

</template>

<script>
    import {mapGetters, mapState} from 'vuex';
    import InboxCard from './InboxCard';

    export default {
        components: {
            'inbox-card': InboxCard
        },
        computed: {
            ...mapState({
                inbox: state => state.inbox.all,
            }),
            ...mapGetters({
                isLoading: 'loaders/isLoading',
            }),
        },
        created () {
            this.$store.dispatch('inbox/loadAll');
        },
        mounted() {
            this.loadScroll();
        },
        methods: {
            filterInboxItemsByType( items, type ) {

                let result = [];

                items.forEach((item) => {
                    if(item.type === type) {
                        result.push(item);
                    }
                });

                return result;
            },
            clickProject(id) {
                this.saveScroll();
                this.$router.push('/inbox/projects/'+id);
            },
            clickDismiss(item) {
                if(confirm('Sind Sie sicher dass Sie diesen Eintrag löschen möchten? Dieser Vorgang kann nicht rückgängig gemacht werden.')) {
                    this.$store.dispatch('inbox/delete', item.id).then(() => {
                        this.$store.dispatch('inbox/loadAll');
                    });
                }
            },
            saveScroll () {
                window.sessionStorage.setItem('regiosuisse.inbox.scrollTop', document.querySelector('.backend-component-content').scrollTop.toString());
            },
            loadScroll () {
                let scrollTop = window.sessionStorage.getItem('regiosuisse.inbox.scrollTop') || 0;
                document.querySelector('.backend-component-content').scrollTop = parseInt(scrollTop);
            },
        },
    }
</script>