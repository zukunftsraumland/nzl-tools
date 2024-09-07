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
        return api.tags.getAll().then((response) => {
            commit('setAll', response.data);
        });
    },

    load ({ commit }, id) {
        return api.tags.get(id).then((response) => {
            commit('set', response.data);
        });
    },

    // New action to create a tag
    create ({ commit }, tagName) {
        return api.tags.create({ name: tagName }).then((response) => {
            commit('addTag', response.data);
            return response.data;  // return the created tag
        });
    },

};

// mutations
const mutations = {

    setAll (state, tags) {
        state.all = tags;
    },

    set (state, tag) {
        state.tag = tag;
    },

    // New mutation to add a new tag to the state
    addTag (state, tag) {
        state.all.push(tag);
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};