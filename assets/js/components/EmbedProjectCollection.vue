<template>

    <div class="embed-project-collection" :class="[$env.INSTANCE_ID+'-project-collection', {'is-responsive': responsive}]">

        <transition name="embed-project-collection-list" mode="out-in">

            <div class="embed-project-collection-list" v-if="!isLoading">

                <div class="embed-project-collection-list-item"
                     v-for="project in projects" :id="'project-'+project.id"
                     :class="{'is-draft': project.isPublic !== true}"
                     @click.stop="clickShowProject(project)">

                    <div class="embed-project-collection-list-item-header">

                        <div class="embed-project-collection-list-item-header-image" v-if="project.images.length" :style="{
                            backgroundImage: 'url('+$env.HOST+'/api/v1/files/view/'+ project.images[0].id +'.' + project.images[0].extension+')'
                        }"></div>

                        <div class="embed-project-collection-list-item-header-image" v-else></div>

                    </div>

                    <div class="embed-project-collection-list-item-content">

                        <h3 class="embed-project-collection-list-item-content-title">
                            {{ translateField(project, 'title', locale) }}
                        </h3>

                        <p class="embed-project-collection-list-item-content-description">
                            {{ $helpers.textExcerpt($helpers.stripHTML(translateField(project, 'description', locale)), 168, '...') }}
                        </p>

                        <div class="embed-project-collection-list-item-content-tags">

                            <div class="embed-project-collection-list-item-content-tags-item"
                                 v-for="topic in project.topics.filter(e => getTopicById(e.id))">
                                {{ translateField(getTopicById(topic.id), 'name', locale) }}
                            </div>

                            <div class="embed-project-collection-list-item-content-tags-item"
                                 v-for="program in project.programs.filter(e => getProgramById(e.id))">
                                {{ translateField(getProgramById(program.id), 'name', locale) }}
                            </div>

                            <div class="embed-project-collection-list-item-content-tags-item"
                                 v-for="state in project.states.filter(e => getStateById(e.id))">
                                {{ translateField(getStateById(state.id), 'name', locale) }}
                            </div>

                            <div class="embed-project-collection-list-item-content-tags-item" v-if="project.startDate">
                                {{ $helpers.formatDate(project.startDate, 'YYYY') }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </transition>

        <transition name="embed-project-collection-overlay" mode="out-in">

            <div class="embed-project-collection-overlay" v-if="project" @click="clickHideProject()">

                <EmbedProjectsView :project="project" :locale="locale" @click.stop
                                   @clickClose="clickHideProject()"></EmbedProjectsView>

            </div>

        </transition>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import EmbedProjectsView from './EmbedProjectsView';
import {track} from '../utils/logger';
import api from '../api';

export default {

    components: {
        EmbedProjectsView,
    },

    data() {
        return {
            isLoading: false,
            projects: [],
            project: null,
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
            states: state => state.states.all,
            topics: state => state.topics.all,
            programs: state => state.programs.all,
            instruments: state => state.instruments.all,
        }),
        ...mapGetters({
            getStateById: 'states/getById',
            getTopicById: 'topics/getById',
            getProgramById: 'programs/getById',
            getInstrumentById: 'instruments/getById',
        }),
    },

    methods: {

        translateField,

        keyUp (event) {

            if(event.keyCode === 27) {
                this.project = null;
            }

        },

        clickShowProject(project) {

            if(!this.disableTelemetry) {
                track('Project Collection Navigation', 'Show Project', {
                    id: project.id,
                    title: translateField(project, 'title', this.locale),
                    collectionId: this.collectionId,
                });
            }

            this.project = project;

        },

        clickHideProject() {

            if(!this.disableTelemetry) {
                track('Project Collection Navigation', 'Hide Project', {
                    id: this.project.id,
                    title: translateField(this.project, 'title', this.locale),
                    collectionId: this.collectionId,
                });
            }

            this.project = null;

        },

        reload() {
            this.isLoading = true;

            return api.projectCollections.getPublic(this.collectionId).then((response) => {

                let collection = response.data;

                this.projects = [
                    ...collection.projects,
                ];

                this.isLoading = false;

                if(!this.disableTelemetry) {
                    track('Project Collection', 'Load', {
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
            this.$store.dispatch('states/loadAll'),
            this.$store.dispatch('instruments/loadAll'),
            this.$store.dispatch('programs/loadAll'),
        ]).then(() => {});
    },

    beforeUnmount() {
        window.removeEventListener('keyup', this.keyUp);
    }

};

</script>