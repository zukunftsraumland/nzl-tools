import api from '../../api';

// initial state
const state = () => ({
    all: [],
    filtered: [],
    employment: {},
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
        commit('loaders/showLoader', 'employments', { root: true });
        return api.employments.getAll().then((response) => {
            commit('loaders/hideLoader', 'employments', { root: true });
            commit('setAll', response.data);
            return response.data;
        });
    },

    loadFiltered ({ commit }, params) {
        commit('loaders/showLoader', 'employments', { root: true });
        return api.employments.getFiltered(params).then((response) => {
            commit('loaders/hideLoader', 'employments', { root: true });
            commit('setFiltered', response.data);
            return response.data;
        });
    },

    load ({ commit }, id) {
        commit('loaders/showLoader', 'employments/'+id, { root: true });
        return api.employments.get(id).then((response) => {
            commit('loaders/hideLoader', 'employments/'+id, { root: true });
            commit('set', response.data);
            return response.data;
        });
    },

    create ({ commit }, payload) {
        commit('loaders/showLoader', 'employments/create', { root: true });
        return api.employments.create(payload).then((response) => {
            commit('loaders/hideLoader', 'employments/create', { root: true });
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
        commit('loaders/showLoader', 'employments/'+payload.id, { root: true });
        return api.employments.update(payload.id, payload).then((response) => {
            commit('loaders/hideLoader', 'employments/'+payload.id, { root: true });
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
        commit('loaders/showLoader', 'employments/'+id, { root: true });
        return api.employments.delete(id).then((response) => {
            commit('loaders/hideLoader', 'employments/'+id, { root: true });
            commit('remove', id);
        });
    },

};

// mutations
const mutations = {

    setAll (state, employments) {
        state.all = employments;
    },

    setFiltered (state, filtered) {
        state.filtered = filtered;
    },

    set (state, employment) {
        if(employment) {
            employment = {
                ...employment,
                translations: typeof employment.translations === 'object' && employment.translations !== null && !Array.isArray(employment.translations) ? employment.translations : {},
            };
        }
        state.employment = employment;
    },

    insert (state, employment) {
        state.all = [...state.all, employment];
    },

    update (state, employment) {
        let existingEmployment = state.all.find(p => p.id === employment.id);
        if(existingEmployment) {
            state.all[state.all.indexOf(existingEmployment)] = employment;
        }
        existingEmployment = state.filtered.find(p => p.id === employment.id);
        if(existingEmployment) {
            state.filtered[state.filtered.indexOf(existingEmployment)] = employment;
        }
    },

    remove (state, id) {
        state.all = state.all.filter((employment) => {
            return parseInt(employment.id) !== parseInt(id);
        });
        state.filtered = state.filtered.filter((employment) => {
            return parseInt(employment.id) !== parseInt(id);
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