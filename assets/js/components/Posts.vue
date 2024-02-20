<template>

    <div class="posts-component">

        <div class="posts-component-title">

            <h2>News</h2>

            <transition name="fade" mode="out-in">
                <div class="loading-indicator" v-if="isLoading('posts')"></div>
            </transition>

            <div class="posts-component-title-actions">
                <router-link :to="'/posts/add'" class="button primary">Neuen Eintrag erstellen</router-link>
            </div>

        </div>

        <div class="posts-component-filter">

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
                        <label for="topics">Thema</label>
                        <div class="select-wrapper">
                            <select id="topics" class="form-control" @change="addFilter({type: 'topic', value: $event.target.value}); $event.target.value = null;">
                                <option></option>
                                <option v-for="topic in topics.filter(topic => !topic.context || topic.context === 'post')">{{topic.name}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="projects-component-filter-tags">
                <div class="tag" v-for="filter of filters" @click="removeFilter({type: filter.type, value: filter.value})">
                    <strong v-if="filter.type === 'topic'">Thema:</strong>
                    {{filter.value}}
                </div>
            </div>

        </div>

        <div class="posts-component-content">

            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Bezeichnung</th>
                    <th>Datum</th>
                    <th>Thema</th>
                </tr>
                </thead>
                <tbody v-if="!posts.length && isLoading('posts')">
                    <tr>
                        <td colspan="4"><em>Einträge werden geladen...</em></td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr v-for="post in posts"
                        class="clickable"
                        :class="{'warning': !post.isPublic}"
                        @click="clickPost(post)">
                        <td>{{ post.id }}</td>
                        <td>{{ translateField(post, 'title', 'de') }}</td>
                        <td>{{ formatDate(post.date) }}</td>
                        <td>{{ formatOneToMany(post.topics, getTopicById) }}</td>
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
            posts: state => state.posts.filtered,
            topics: state => state.topics.all,
        }),
        ...mapGetters({
            isLoading: 'loaders/isLoading',
            getPostById: 'posts/getById',
            getTopicById: 'topics/getById',
        }),
    },
    methods: {
        changeForm () {
            this.saveFilter();
            this.reloadPosts();
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
        reloadPosts () {
            return this.$store.dispatch('posts/loadFiltered', this.getFilterParams());
        },
        clickPost (post) {
            this.$router.push({
                path: '/posts/'+post.id+'/edit'
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
            window.sessionStorage.setItem('regiosuisse.posts.filters', JSON.stringify(this.filters));
            window.sessionStorage.setItem('regiosuisse.posts.term', this.term);
        },
        loadFilter () {
            this.filters = JSON.parse(window.sessionStorage.getItem('regiosuisse.posts.filters') || '[]');
            this.term = window.sessionStorage.getItem('regiosuisse.posts.term') || '';
        },
        translateField,
    },
    created () {
        this.loadFilter();
        this.reloadPosts();
    },
}
</script>