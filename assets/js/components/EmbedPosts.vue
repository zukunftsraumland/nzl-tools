<template>

    <div class="embed-posts" :class="[$env.INSTANCE_ID+'-posts', {'is-responsive': responsive}]" @click.stop="clickInside">

        <div class="embed-posts-search">

            <div class="embed-posts-search-input">
                <input type="text" :placeholder="$t('Suchbegriff', locale)" v-model="term"
                       :class="{'has-value': term}"
                       @change="changeSearchTerm()"
                       @keyup="$event.keyCode === 13 ? changeSearchTerm() : null">
                <div class="embed-posts-search-input-icon" @click.stop="term = null; changeSearchTerm()"></div>
            </div>

        </div>

        <div class="embed-posts-filters">

            <div class="embed-posts-filters-select" data-filter-type="topics">

                <div class="embed-posts-filters-select-label"
                     @click.stop="clickFilterSelect('topic')">{{ $t('Thema', locale) }}</div>

                <div class="embed-posts-filters-select-icon"
                     :class="{'is-active': activeFilterSelect === 'topic'}"></div>

                <transition name="embed-posts-filters-select-options" mode="out-in">

                    <div class="embed-posts-filters-select-options" v-if="activeFilterSelect === 'topic'">

                        <div class="embed-posts-filters-select-options-item"
                             v-for="topic in topics"
                             :class="{ 'is-selected': isFilterSelected({ type: 'topic', entity: topic }) }"
                             @click.stop="clickToggleFilter({ type: 'topic', entity: topic })">
                            {{ translateField(topic, 'name', locale) }}
                        </div>

                    </div>

                </transition>

            </div>

            <div class="embed-posts-filters-list">

                <div class="embed-posts-filters-list-item"
                     v-for="filter in filters"
                     @click.stop="clickToggleFilter(filter)">{{ translateField(filter.entity, 'name', locale) }}</div>

            </div>

        </div>

        <transition name="embed-posts-list" mode="out-in">

            <div class="embed-posts-list" v-if="!isLoading">

                <div class="embed-posts-list-item"
                     v-for="post in posts" :id="'post-'+post.id"
                     :class="{'is-draft': post.isPublic !== true}"
                     @click.stop="clickShowPost(post)">

                    <div class="embed-posts-list-item-header" v-if="!disableThumbnails">

                        <div class="embed-posts-list-item-header-image" v-if="post.images.length" :style="{
                            backgroundImage: 'url('+$env.HOST+'/api/v1/files/view/'+ post.images[0].id +'.' + post.images[0].extension+')'
                        }"></div>

                        <div class="embed-posts-list-item-header-image" v-else></div>

                    </div>

                    <div class="embed-posts-list-item-content">

                        <h3 class="embed-posts-list-item-content-title">
                            {{ translateField(post, 'name', locale) }}
                        </h3>

                        <p class="embed-posts-list-item-content-description">
                            {{ translateField(post, 'description', locale) }}
                        </p>

                        <div class="embed-posts-list-item-content-tags">

                            <div class="embed-posts-list-item-content-tags-item"
                                 v-for="topic in post.topics.filter(e => getTopicById(e.id))">
                                {{ translateField(getTopicById(topic.id), 'name', locale) }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </transition>

        <transition name="embed-posts-overlay" mode="out-in">

            <div class="embed-posts-overlay" v-if="post" @click="clickHidePost()">

                <EmbedPostsView :post="post" :locale="locale" @click.stop
                                   @clickClose="clickHidePost()"></EmbedPostsView>

            </div>

        </transition>

    </div>

</template>

<script>

import {mapGetters, mapState} from 'vuex';
import { translateField } from '../utils/filters';
import EmbedPostsView from './EmbedPostsView';
import {track, trackDevice, trackPageView} from '../utils/logger';

export default {

    components: {
        EmbedPostsView,
    },

    data() {
        return {
            isLoading: false,
            posts: [],
            term: '',
            filters: [],
            activeFilterSelect: null,
            post: null,
        };
    },

    computed: {
        locale () {
            return this.$clientOptions?.locale || 'de';
        },
        responsive () {
            return this.$clientOptions?.responsive ?? true;
        },
        fixedFilters () {
            return this.$clientOptions?.fixedFilters || [];
        },
        disableTelemetry () {
            return this.$clientOptions?.disableTelemetry || false;
        },
        disableThumbnails () {
            return this.$clientOptions?.disableThumbnails || false;
        },
        history () {
            return this.$clientOptions?.history || false;
        },
        historyMode () {
            return this.$clientOptions?.history?.mode || 'query';
        },
        historyBase () {
            return this.$clientOptions?.history?.base || '';
        },
        ...mapState({
            topics: function (state) {
                return state.topics.all
                    .filter(e => !e.context || e.context === 'post')
                    .map(this.$clientOptions?.middleware?.mapTopics || (e => e))
                    .filter(this.$clientOptions?.middleware?.filterTopics  || (e => e.isPublic))
                    .sort(this.$clientOptions?.middleware?.sortTopics  || ((a, b) => a.position - b.position));
            },
        }),
        ...mapGetters({
            getTopicById: 'topics/getById',
        }),
    },

    methods: {

        translateField,

        keyUp (event) {

            if(event.keyCode === 27) {
                this.activeFilterSelect = null;
                this.post = null;
            }

        },

        clickOutside (event) {

            this.activeFilterSelect = null;

        },

        clickInside (event) {

            this.activeFilterSelect = null;

        },

        getFilterParams() {

            let params = {};
            params.term = this.term;

            let filters = [...this.filters, ...(this.fixedFilters || [])];

            for(let filter of filters) {
                params[filter.type] = [];
            }

            for(let filter of filters) {
                params[filter.type].push(filter.entity?.id || filter.entity?.name);
            }

            return params;

        },

        clickFilterSelect(name) {

            if(this.activeFilterSelect === name) {

                if(!this.disableTelemetry) {
                    track('Post Filter', 'Hide', name);
                }

                return this.activeFilterSelect = null;
            }

            if(!this.disableTelemetry) {
                track('Post Filter', 'Show', name);
            }

            this.activeFilterSelect = name;

        },

        clickToggleFilter(filter) {
            this.activeFilterSelect = null;

            let index = this.filters.findIndex(e => e.type === filter.type && e.entity.id === filter.entity.id);

            if(index !== -1) {

                this.filters.splice(index, 1);
                this.reload();

                if(this.history) {
                    window.history.replaceState(null, null, this.getHistoryQueryString());
                }

                if(!this.disableTelemetry) {
                    track('Post Filter', 'Disable', {
                        type: filter.type,
                        id: filter.entity.id,
                    });
                }

                return;

            }

            this.filters.push(filter);

            this.reload();

            if(this.history) {
                window.history.replaceState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Post Filter', 'Enable', {
                    type: filter.type,
                    id: filter.entity.id,
                });
            }
        },

        isFilterSelected(filter) {
            return this.filters.find(e => e.type === filter.type && e.entity.id === filter.entity.id);
        },

        changeSearchTerm() {

            this.reload();

            if(this.history) {
                window.history.replaceState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Post Search', 'Change', this.term);
            }

        },

        clickShowPost(post) {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString(post));
            }

            if(!this.disableTelemetry) {
                track('Post Navigation', 'Show Post', {
                    id: post.id,
                    name: translateField(post, 'name', this.locale),
                });
            }

            this.post = post;

        },

        clickHidePost() {

            if(this.history) {
                window.history.pushState(null, null, this.getHistoryQueryString());
            }

            if(!this.disableTelemetry) {
                track('Post Navigation', 'Hide Post', {
                    id: this.post.id,
                    name: translateField(this.post, 'name', this.locale),
                });
            }

            this.post = null;

        },

        popState(event) {

            this.post = null;

            if(this.getUrlParams()['post-id']) {
                this.$store.dispatch('posts/load', this.getUrlParams()['post-id']).then((post) => {
                    this.post = post;
                });
            }

        },

        getUrlParams () {
            let queryString = window.location.search;

            if(this.historyMode === 'hash') {
                queryString = window.location.hash.substring(1);
            }

            let urlParams = new URLSearchParams(queryString);
            let result = {};

            for(const [key, value] of urlParams) {

                let k = key.split('[')[0];

                if(!['postTypes', 'languages', 'locations'].includes(k)) {
                    result[k] = value;
                    continue;
                }

                if(!result[k]) {
                    result[k] = [];
                }

                result[k].push(value);

            }

            return result;
        },

        getHistoryQueryString(post) {

            let result = [];

            if(post) {
                result.push('post-id='+post.id+'&name='+encodeURIComponent(translateField(post, 'name', this.locale)));
            }

            if(this.term) {
                result.push('term='+encodeURIComponent(this.term));
            }

            for(let filter of this.filters) {
                result.push(filter.type+'s[]='+encodeURIComponent(translateField(filter.entity, 'name', this.locale)))
            }

            result = result.join('&');

            if(!result) {
                return this.historyBase;
            }

            return this.historyBase + (this.historyMode === 'hash' ? '#' : '') + '?' + result;

        },

        applyFiltersFromUrlParameters() {

            this.term = this.getUrlParams()['term'];

            let filters = [];

            ['topic'].forEach((key) => {

                let collection = key+'s';

                if(!this.getUrlParams()[key+'s']) {
                    return;
                }

                this.getUrlParams()[key+'s'].forEach((f) => {
                    let entity = this[collection].find(e => e.id === f || e.name === f || translateField(e, 'name', this.locale) === f);

                    if(!entity) {
                        return;
                    }

                    filters.push({
                        type: key,
                        entity: entity,
                    });
                });

            });

            if(filters.length) {
                this.filters = filters;
                this.reload();
            }

        },

        reload() {
            this.isLoading = true;

            return this.$store.dispatch('posts/loadFiltered', this.getFilterParams()).then((posts) => {

                this.posts = [
                    ...posts,
                ];

                this.isLoading = false;

            });
        },

    },

    created() {
        this.filters = this.$clientOptions?.defaultFilters || [];

        if(this.history && this.getUrlParams()['term']) {
            this.term = this.getUrlParams()['term'];
        }

        this.reload();

        Promise.all([
            this.$store.dispatch('topics/loadAll'),
        ]).then(() => {

            this.filters = this.filters
                .filter((filter) => {
                    return ['topic'].includes(filter.type);
                })
                .map((filter) => {
                    return {
                        type: filter.type,
                        entity: {
                            ...this[filter.type+'s'].find(e => e.id === filter.entity.id),
                        },
                    }
                });

            if(this.history) {
                this.applyFiltersFromUrlParameters();
            }

        });

    },

    mounted() {
        window.addEventListener('click', this.clickOutside);
        window.addEventListener('keyup', this.keyUp);

        if(this.history && this.getUrlParams()['post-id']) {
            this.$store.dispatch('posts/load', this.getUrlParams()['post-id']).then((post) => {
                this.post = post;
            });
        }

        if(this.history) {
            window.addEventListener('popstate', this.popState);
        }

        if(!this.disableTelemetry) {
            trackDevice();
            trackPageView();
        }
    },

    beforeUnmount() {
        window.removeEventListener('click', this.clickOutside);
        window.removeEventListener('keyup', this.keyUp);
        window.removeEventListener('popstate', this.popState);
    }

};

</script>