<template>

    <div class="project-collections-component">

        <div class="project-collections-component-form">

            <div class="project-collections-component-form-header">

                <h3>Projektkollektionen</h3>

                <div class="project-collections-component-form-header-actions">
                    <router-link :to="'/project-collections/add'" class="button primary">Neue Kollektion erstellen</router-link>
                </div>

            </div>

        </div>

        <div class="project-collections-component-list">

            <router-link class="project-collections-component-list-item"
                         v-for="projectCollection in projectCollections"
                         tag="div"
                         :to="'/project-collections/'+projectCollection.id+'/edit'"
            >

                <div class="project-collections-component-list-item-name">
                    <h3>{{ projectCollection.title }} <span class="material-icons" v-if="projectCollection.isDynamic">auto_fix_high</span></h3>
                    <p v-if="projectCollection.description">{{ projectCollection.description }}</p>
                </div>

                <div class="project-collections-component-list-item-count" v-if="projectCollection.isDynamic">
                    {{ projectCollection.filters.length }} Filter ausgewählt (Dynamisch)
                </div>

                <div class="project-collections-component-list-item-count" v-else>
                    {{ projectCollection.selection.length }} Projekte ausgewählt
                </div>

            </router-link>

        </div>

    </div>

</template>

<script>
    import { mapState, mapGetters } from 'vuex';
    import { filterProjects } from '../utils/filters';

    export default {
        data () {
            return {};
        },
        computed: {
            ...mapState({
                projects: state => state.projects.all,
                projectCollections: state => state.projectCollections.all,
            }),
            ...mapGetters({
                getProjectById: 'projects/getById',
            }),
        },
        created () {
            this.$store.dispatch('projectCollections/loadAll');

            if(!this.projects.length) {
                // this.$store.dispatch('projects/loadAll');
            }
        },
        methods: {
            filterProjects (projects, filters, term) {
                return filterProjects(projects.map((p) => {
                    return this.getProjectById(p.id);
                }).filter(p => p), filters, term);
            },
        },
    }
</script>