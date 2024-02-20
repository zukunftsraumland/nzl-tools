import api from '../../api';

// initial state
const state = () => ({
    all: [],
    contactGroup: null,
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
        return api.contactGroups.getAll().then((response) => {
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        return api.contactGroups.get(id).then((response) => {
            commit('set', response.data);
        });
    },

};

// mutations
const mutations = {

    setAll (state, contactGroups) {
        state.all = contactGroups;
    },

    set (state, contactGroup) {
        state.contactGroup = contactGroup;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};