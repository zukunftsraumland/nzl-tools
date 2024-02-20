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
        return api.instruments.getAll().then((response) => {
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        return api.instruments.get(id).then((response) => {
            commit('set', response.data);
        });
    },

};

// mutations
const mutations = {

    setAll (state, instruments) {
        state.all = instruments;
    },

    set (state, instrument) {
        state.instrument = instrument;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};