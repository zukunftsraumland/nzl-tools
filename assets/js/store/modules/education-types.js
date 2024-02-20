import api from '../../api';

// initial state
const state = () => ({
    all: [],
    educationType: null,
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
        return api.educationTypes.getAll().then((response) => {
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        return api.educationTypes.get(id).then((response) => {
            commit('set', response.data);
        });
    },

};

// mutations
const mutations = {

    setAll (state, educationTypes) {
        state.all = educationTypes;
    },

    set (state, educationType) {
        state.educationType = educationType;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};