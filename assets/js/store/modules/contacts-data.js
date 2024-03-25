import api from '../../api';

// initial state
const state = () => ({
    filtered: [],
});

// getters
const getters = {};

// actions
const actions = {

    loadFiltered ({ commit }, params) {
        commit('loaders/showLoader', 'contactsData', { root: true });
        return api.contactsData.getFiltered(params).then((response) => {
            commit('loaders/hideLoader', 'contactsData', { root: true });
            commit('setFiltered', response.data);
            return response.data;
        });
    },

};

// mutations
const mutations = {

    setFiltered (state, filtered) {
        state.filtered = filtered;
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};