import api from '../../api';

// initial state
const state = () => ({
    all: [],
    interactiveGraphic: {},
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
        commit('loaders/showLoader', 'interactiveGraphics', { root: true });
        return api.interactiveGraphics.getAll().then((response) => {
            commit('loaders/hideLoader', 'interactiveGraphics', { root: true });
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'interactiveGraphics/'+id, { root: true });
        return api.interactiveGraphics.get(id).then((response) => {
            commit('loaders/hideLoader', 'interactiveGraphics/'+id, { root: true });
            commit('set', response.data);
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'interactiveGraphics/create', { root: true });
        return api.interactiveGraphics.create(payload).then((response) => {
            commit('loaders/hideLoader', 'interactiveGraphics/create', { root: true });
            commit('set', response.data);
        });
    },

    update ({ commit }, payload) {
        commit('loaders/showLoader', 'interactiveGraphics/'+payload.id, { root: true });
        return api.interactiveGraphics.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'interactiveGraphics/'+payload.id, { root: true });
            commit('set', response.data);
        });
    },

    delete ({ commit }, id) {
        commit('loaders/showLoader', 'interactiveGraphics/'+id, { root: true });
        return api.interactiveGraphics.delete(id).then((response) => {
            commit('loaders/hideLoader', 'interactiveGraphics/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, interactiveGraphics) {
        state.all = interactiveGraphics;
    },

    set (state, interactiveGraphic) {
        state.interactiveGraphic = interactiveGraphic;
    },

    remove (state, id) {
        state.all = state.all.filter((interactiveGraphic) => {
            return parseInt(interactiveGraphic.id) !== parseInt(id);
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