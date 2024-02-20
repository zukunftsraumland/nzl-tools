import api from '../../api';

// initial state
const state = () => ({
    all: [],
    project: {},
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
        commit('loaders/showLoader', 'files', { root: true });
        return api.files.getAll().then((response) => {
            commit('loaders/hideLoader', 'files', { root: true });
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'files/'+id, { root: true });
        return api.files.get(id).then((response) => {
            commit('loaders/hideLoader', 'files/'+id, { root: true });
            commit('set', response.data);
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'files/create', { root: true });
        return api.files.create(payload).then((response) => {
            commit('loaders/hideLoader', 'files/create', { root: true });
            commit('set', response.data);
            return response.data;
        });
    },

    update ({ commit }, payload) {
        commit('loaders/showLoader', 'files/'+payload.id, { root: true });
        return api.files.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'files/'+payload.id, { root: true });
            commit('set', response.data);
        });
    },

    delete ({ commit }, id) {
        commit('loaders/showLoader', 'files/'+id, { root: true });
        return api.files.delete(id).then((response) => {
            commit('loaders/hideLoader', 'files/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, files) {
        state.all = files;
    },

    set (state, project) {
        state.project = project;
    },

    remove (state, id) {
        state.all = state.all.filter((project) => {
            return parseInt(project.id) !== parseInt(id);
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