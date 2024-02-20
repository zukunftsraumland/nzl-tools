import api from '../../api';

// initial state
const state = () => ({
    all: [],
    item: {},
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
        commit('loaders/showLoader', 'inbox', { root: true });
        return api.inbox.getAll().then((response) => {
            commit('loaders/hideLoader', 'inbox', { root: true });
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'inbox/'+id, { root: true });
        return api.inbox.get(id).then((response) => {
            commit('loaders/hideLoader', 'inbox/'+id, { root: true });
            commit('set', response.data);
        });
    },

    delete ({ commit }, id) {
        commit('loaders/showLoader', 'inbox/'+id, { root: true });
        return api.inbox.delete(id).then((response) => {
            commit('loaders/hideLoader', 'inbox/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, inbox) {
        state.all = inbox;
    },

    set (state, item) {
        state.item = item;
    },

    insert (state, item) {
        state.all = [...state.all, item];
    },

    update (state, item) {
        let existingItem = state.all.find(p => p.id === item.id);
        if(existingItem) {
            state.all[state.all.indexOf(existingItem)] = item;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((item) => {
            return parseInt(item.id) !== parseInt(id);
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