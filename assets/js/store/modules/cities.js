import api from '../../api';

// initial state
const state = () => ({
    all: [],
    city: null,
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
        return api.cities.getAll().then((response) => {
            commit('setAll', response.data);
            return response.data;
        });
    },

    load ({ commit }, id) {
        return api.cities.get(id).then((response) => {
            commit('set', response.data);
            return response.data;
        });
    },

};

// mutations
const mutations = {

    setAll (state, cities) {
        state.all = cities;
    },

    set (state, city) {
        state.city = city;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};