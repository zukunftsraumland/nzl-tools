import api from '../../api';

// initial state
const state = () => ({
    all: [],
    projectCollection: {},
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
        commit('loaders/showLoader', 'projectCollections', { root: true });
        return api.projectCollections.getAll().then((response) => {
            commit('loaders/hideLoader', 'projectCollections', { root: true });
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'projectCollections/'+id, { root: true });
        return api.projectCollections.get(id).then((response) => {
            commit('loaders/hideLoader', 'projectCollections/'+id, { root: true });
            commit('set', response.data);
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'projectCollections/create', { root: true });
        return api.projectCollections.create(payload).then((response) => {
            commit('loaders/hideLoader', 'projectCollections/create', { root: true });
            commit('set', response.data);
        });
    },

    update ({ commit }, payload) {
        commit('loaders/showLoader', 'projectCollections/'+payload.id, { root: true });
        return api.projectCollections.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'projectCollections/'+payload.id, { root: true });
            commit('set', response.data);
        });
    },

    delete ({ commit }, id) {
        commit('loaders/showLoader', 'projectCollections/'+id, { root: true });
        return api.projectCollections.delete(id).then((response) => {
            commit('loaders/hideLoader', 'projectCollections/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, projectCollections) {
        state.all = projectCollections;
    },

    set (state, projectCollection) {
        state.projectCollection = projectCollection;
    },

    remove (state, id) {
        state.all = state.all.filter((projectCollection) => {
            return parseInt(projectCollection.id) !== parseInt(id);
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