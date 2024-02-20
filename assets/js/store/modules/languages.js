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
        return api.languages.getAll().then((response) => {
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        return api.languages.get(id).then((response) => {
            commit('set', response.data);
        });
    },

};

// mutations
const mutations = {

    setAll (state, languages) {
        state.all = languages;
    },

    set (state, language) {
        state.language = language;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};