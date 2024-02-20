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
        return api.locations.getAll().then((response) => {
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        return api.locations.get(id).then((response) => {
            commit('set', response.data);
        });
    },

};

// mutations
const mutations = {

    setAll (state, locations) {
        state.all = locations;
    },

    set (state, location) {
        state.location = location;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};