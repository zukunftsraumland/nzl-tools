import api from '../../api';

// initial state
const state = () => ({
    all: [],
    filtered: [],
    post: {},
});

// getters
const getters = {

    getById: (state) => (id) => {
        return state.all.find(item => item.id === id);
    },

};

// actions
const actions = {

    loadAll ({ commit }) {
        commit('loaders/showLoader', 'posts', { root: true });
        return api.posts.getAll().then((response) => {
            commit('loaders/hideLoader', 'posts', { root: true });
            commit('setAll', response.data);
            return response.data;
        });
    },

    loadFiltered ({ commit }, params) {
        commit('loaders/showLoader', 'posts', { root: true });
        return api.posts.getFiltered(params).then((response) => {
            commit('loaders/hideLoader', 'posts', { root: true });
            commit('setFiltered', response.data);
            return response.data;
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'posts/'+id, { root: true });
        return api.posts.get(id).then((response) => {
            commit('loaders/hideLoader', 'posts/'+id, { root: true });
            commit('set', response.data);
            return response.data;
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'posts/create', { root: true });
        return api.posts.create(payload).then((response) => {
            commit('loaders/hideLoader', 'posts/create', { root: true });
            if(payload.addToInbox) {
                if(payload.inboxId) {
                    commit('inbox/update', response.data, { root: true });
                } else {
                    commit('inbox/insert', response.data, { root: true });
                }
            } else {
                commit('insert', response.data);
                commit('set', response.data);
            }
        });
    },

    update ({ commit }, payload) {
        commit('loaders/showLoader', 'posts/'+payload.id, { root: true });
        return api.posts.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'posts/'+payload.id, { root: true });
            if(payload.addToInbox) {
                if(payload.inboxId) {
                    commit('inbox/update', response.data, { root: true });
                } else {
                    commit('inbox/insert', response.data, { root: true });
                }
            } else {
                commit('update', response.data);
                commit('set', response.data);
            }
        });
    },

    delete ({ commit }, id) {
        commit('loaders/showLoader', 'posts/'+id, { root: true });
        return api.posts.delete(id).then((response) => {
            commit('loaders/hideLoader', 'posts/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, posts) {
        state.all = posts;
    },

    setFiltered (state, filtered) {
        state.filtered = filtered;
    },

    set (state, post) {
        if(post) {
            post = {
                ...post,
                translations: typeof post.translations === 'object' && post.translations !== null && !Array.isArray(post.translations) ? post.translations : {},
            };
        }
        state.post = post;
    },

    insert (state, post) {
        state.all = [...state.all, post];
    },

    update (state, post) {
        let existingPost = state.all.find(p => p.id === post.id);
        if(existingPost) {
            state.all[state.all.indexOf(existingPost)] = post;
        }
        existingPost = state.filtered.find(p => p.id === post.id);
        if(existingPost) {
            state.filtered[state.filtered.indexOf(existingPost)] = post;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((post) => {
            return parseInt(post.id) !== parseInt(id);
        });
        state.filtered = state.filtered.filter((post) => {
            return parseInt(post.id) !== parseInt(id);
        });
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};