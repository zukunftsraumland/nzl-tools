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
        return api.localWorkgroups.getAll().then((response) => {
            commit('setAll', response.data);
        });
    },

};

// mutations
const mutations = {

    setAll (state, localWorkgroups) {
        state.all = localWorkgroups;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};