import api from '../../api';

// initial state
const state = () => ({
    all: [],
    beneficiary: null,
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
        return api.beneficiaries.getAll().then((response) => {
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        return api.beneficiaries.get(id).then((response) => {
            commit('set', response.data);
        });
    },

};

// mutations
const mutations = {

    setAll (state, beneficiaries) {
        state.all = beneficiaries;
    },

    set (state, beneficiary) {
        state.beneficiary = beneficiary;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};