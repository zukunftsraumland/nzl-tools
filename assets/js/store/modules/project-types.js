import api from '../../api';

// initial state
const state = () => ({
    all: [],
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
        return api.projectTypes.getAll().then((response) => {
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        return api.projectTypes.get(id).then((response) => {
            commit('set', response.data);
        });
    },

};

// mutations
const mutations = {

    setAll (state, projectTypes) {
        state.all = projectTypes;
    },

    set (state, projectType) {
        state.projectType = projectType;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};