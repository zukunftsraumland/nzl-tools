import api from '../../api';

// initial state
const state = () => ({
    all: [],
    filtered: [],
    education: {},
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
        commit('loaders/showLoader', 'educations', { root: true });
        return api.educations.getAll().then((response) => {
            commit('loaders/hideLoader', 'educations', { root: true });
            commit('setAll', response.data);
            return response.data;
        });
    },

    loadFiltered ({ commit }, params) {
        commit('loaders/showLoader', 'educations', { root: true });
        return api.educations.getFiltered(params).then((response) => {
            commit('loaders/hideLoader', 'educations', { root: true });
            commit('setFiltered', response.data);
            return response.data;
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'educations/'+id, { root: true });
        return api.educations.get(id).then((response) => {
            commit('loaders/hideLoader', 'educations/'+id, { root: true });
            commit('set', response.data);
            return response.data;
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'educations/create', { root: true });
        return api.educations.create(payload).then((response) => {
            commit('loaders/hideLoader', 'educations/create', { root: true });
            if(payload.addToInbox) {
                if(payload.inboxId) {
                    commit('inbox/update', response.data, { root: true });
                } else {
                    commit('inbox/insert', response.data, { root: true });
                }
            } else {
                commit('insert', response.data);
                commit('set', response.data);
            }
        });
    },

    update ({ commit }, payload) {
        commit('loaders/showLoader', 'educations/'+payload.id, { root: true });
        return api.educations.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'educations/'+payload.id, { root: true });
            if(payload.addToInbox) {
                if(payload.inboxId) {
                    commit('inbox/update', response.data, { root: true });
                } else {
                    commit('inbox/insert', response.data, { root: true });
                }
            } else {
                commit('update', response.data);
                commit('set', response.data);
            }
        });
    },

    delete ({ commit }, id) {
        commit('loaders/showLoader', 'educations/'+id, { root: true });
        return api.educations.delete(id).then((response) => {
            commit('loaders/hideLoader', 'educations/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, educations) {
        state.all = educations;
    },

    setFiltered (state, filtered) {
        state.filtered = filtered;
    },

    set (state, education) {
        if(education) {
            education = {
                ...education,
                translations: typeof education.translations === 'object' && education.translations !== null && !Array.isArray(education.translations) ? education.translations : {},
            };
        }
        state.education = education;
    },

    insert (state, education) {
        state.all = [...state.all, education];
    },

    update (state, education) {
        let existingEducation = state.all.find(p => p.id === education.id);
        if(existingEducation) {
            state.all[state.all.indexOf(existingEducation)] = education;
        }
        existingEducation = state.filtered.find(p => p.id === education.id);
        if(existingEducation) {
            state.filtered[state.filtered.indexOf(existingEducation)] = education;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((education) => {
            return parseInt(education.id) !== parseInt(id);
        });
        state.filtered = state.filtered.filter((education) => {
            return parseInt(education.id) !== parseInt(id);
        });
    },

};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};