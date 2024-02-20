import api from '../../api';

// initial state
const state = () => ({
    all: [],
    eventCollection: {},
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
        commit('loaders/showLoader', 'eventCollections', { root: true });
        return api.eventCollections.getAll().then((response) => {
            commit('loaders/hideLoader', 'eventCollections', { root: true });
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'eventCollections/'+id, { root: true });
        return api.eventCollections.get(id).then((response) => {
            commit('loaders/hideLoader', 'eventCollections/'+id, { root: true });
            commit('set', response.data);
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'eventCollections/create', { root: true });
        return api.eventCollections.create(payload).then((response) => {
            commit('loaders/hideLoader', 'eventCollections/create', { root: true });
            commit('set', response.data);
        });
    },

    update ({ commit }, payload) {
        commit('loaders/showLoader', 'eventCollections/'+payload.id, { root: true });
        return api.eventCollections.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'eventCollections/'+payload.id, { root: true });
            commit('set', response.data);
        });
    },

    delete ({ commit }, id) {
        commit('loaders/showLoader', 'eventCollections/'+id, { root: true });
        return api.eventCollections.delete(id).then((response) => {
            commit('loaders/hideLoader', 'eventCollections/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, eventCollections) {
        state.all = eventCollections;
    },

    set (state, eventCollection) {
        state.eventCollection = eventCollection;
    },

    remove (state, id) {
        state.all = state.all.filter((eventCollection) => {
            return parseInt(eventCollection.id) !== parseInt(id);
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